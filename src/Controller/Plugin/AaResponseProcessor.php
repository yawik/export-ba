<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Controller\Plugin;

use ExportBA\Client\AaClient;
use ExportBA\Entity\JobMetaData;
use ExportBA\Entity\JobMetaStatus;
use ExportBA\Filter\JobId;
use Jobs\Entity\Job;
use ExportBA\Repository\JobMetaRepository;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaResponseProcessor extends AbstractPlugin
{
    private $client;
    private $queue;
    private $name;
    private $supplierId;
    private $files = [];
    /**
     * @var JobMetaRepository
     */
    private $repository;

    public function __construct(AaClient $client, $queue, $name, $supplierId, $repository)
    {
        $this->client = $client;
        $this->queue = $queue;
        $this->supplierId = $supplierId;
        $this->name = $name;
        $this->repository = $repository;
        $this->init();
    }

    private function init()
    {
        $this->client->open($this->name);

        $html = $this->client->get();

        $dom = new \DomDocument("1.0", 'UTF-8');
        $dom->loadHTML($html['result']);

        foreach ($dom->getElementsByTagName('a') as $node) {
            /** @var \DOMElement $node */
            if (!$node->hasAttribute('href')) {
                continue;
            }
            $href = $node->getAttribute('href');
            if (strpos($href, 'ES' . $this->supplierId . '_') !== 0) {
                continue;
            }

            $this->files[] = $href;
        }
    }

    public function process()
    {
        $failed = [];

        while ($file = $this->queue->pop()) {
            echo "Process $file:\n";
            [$responseFiles, $uploadDate] = $this->getResponseFiles($file);
            if ($responseFiles === false) {
                echo "No response files found.\n";
                $failed[] = $file;
                continue;
            }

            foreach ($responseFiles as $responseFile) {
                echo "-- Response file: $responseFile \n";
                $responseFile = $this->client->download($responseFile);
                // phpcs:ignore
                if (!$responseFile) { continue; }

                $content = file_get_contents($responseFile['file']);

                if ('' == trim($content)) {
                    continue;
                }

                $xml = new \SimpleXMLElement($content);

                foreach ($xml->ErrorInformation->ErrorMessage as $errXml) {
                    if ('FLR_XML_Structure' == $errXml->ErrorCode) {
                        echo '!!!! XML Structure error:' . PHP_EOL;
                        echo $errXml->AdditionalInformation . PHP_EOL;
                        echo '!!!! XML Structure error:' . PHP_EOL;
                        continue 2;
                    }

                    $aaId = (string) $errXml->ReferenceId;
                    $error = (string) $errXml->AdditionalInformation;
                    $id = JobId::fromAaId($aaId);

                    /** @var \ExportBA\Entity\JobMetaData $meta */
                    $meta = $this->repository->getMetaDataFor($id);

                    if ($errXml->ErrorCode == 'FLR_DataStore_130') {
                        if ($meta->hasStatus(JobMetaStatus::PENDING_OFFLINE)) {
                            echo "Job [ " . $meta->getJobId() . " ] already deleted. update status to OFFLINE.\n";
                            $meta->updateStatus(JobMetaStatus::OFFLINE, 'Was already offline on AA.');
                        } elseif ($meta->hasStatus(JobMetaStatus::ONLINE)) {
                            echo "Job [ " . $meta->getJobId() . " ] does not exist on AA. Set status to NEW.\n";
                            $meta->updateStatus(JobMetaStatus::NEW, 'Did not exist on AA yet.');
                        }
                        continue;
                    }

                    if ($errXml->ErrorCode == 'FLR_DataStore_110') {
                        echo "Job [ " . $meta->getJobId() . " ] already online. Set actrion to update.\n";
                        $meta->updateStatus(JobMetaStatus::ONLINE, 'Already online on AA.');
                        $meta->setForceUpdate();
                        continue;
                    }

                    $meta->error($error);
                    echo 'Job [' . $meta->getJobId() . '] has errors. Set status to ERROR' . PHP_EOL;
                }
            }

            $this->repository->getDocumentManager()->flush();

            echo "-- Process valid jobs\n";
            $metas = $this->repository->createQueryBuilder()
                ->field('status.name')->in([
                    JobMetaStatus::PENDING_ONLINE,
                    JobMetaStatus::PENDING_OFFLINE,
                ])
                ->field('uploadDate')->equals($uploadDate)
                ->getQuery()->execute();

            foreach ($metas as $meta) {
                $meta = $meta->receive();
                echo 'Job [ ' . $meta->getJobId() . ' ] is ' . ($meta->hasStatus(JobMetaStatus::ONLINE) ? 'online' : 'offline') . PHP_EOL;
            }

            $this->repository->getDocumentManager()->flush();
        }

        foreach ($failed as $file) {
            $this->queue->push($file);
        }

        $this->repository->getDocumentManager()->flush();
    }

    private function getResponseFiles($file)
    {
        $files = [];
        [0 => $prefix, 1 => $date, 2 => $time] = explode('_', basename($file));
        $prefix = str_replace('DS', 'ES', $prefix) . '_' . $date . '_' . $time;

        foreach ($this->files as $responseFile) {
            if (strpos($responseFile, $prefix) === 0) {
                echo "Found response file: $responseFile\n";
                $files[] = $responseFile;
            }
        }

        return [$files ?: false, "{$date}_{$time}"];
    }
}
