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
use ExportBA\Filter\JobId;
use Jobs\Entity\Job;
use Jobs\Repository\Job as JobRepository;
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
     * @var JobRepository
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

                    /** @var Job $job */
                    $job = $this->repository->find($id);
                    if (!$job) {
                        echo 'No job for id ', $id, PHP_EOL;
                        continue;
                    }
                    $meta = JobMetaData::fromJob($job);

                    if ($errXml->ErrorCode == 'FLR_DataStore_130') {
                        if ($meta->isPendingOffline()) {
                            echo "Job [ $id ] already deleted. update status to OFFLINE.\n";
                            $meta->withStatus(JobMetaData::STATUS_OFFLINE, 'Was already offline on AA.')->storeIn($job);
                        } elseif ($meta->isPendingOnline()) {
                            echo "Job [ $id ] does not exist on AA. Set status to NEW.\n";
                            $meta->withStatus(JobMetaData::STATUS_NEW, 'Did not exist on AA yet.')->storeIn($job);
                        }
                        continue;
                    }

                    if ($errXml->ErrorCode == 'FLR_DataStore_110') {
                        echo "Job [ $id ] already online. Set actrion to update.\n";
                        $meta->withStatus(JobMetaData::STATUS_ONLINE, 'Already online on AA.')->storeIn($job);
                        continue;
                    }

                    $meta->withStatus(JobMetaData::STATUS_ERROR, $error)->storeIn($job);
                    echo 'Job [' . $id . '] has errors. Set status to ERROR' . PHP_EOL;
                }
            }

            $this->repository->getDocumentManager()->flush();

            echo "-- Process valid jobs\n";
            $jobs = $this->repository->createQueryBuilder()
                ->field('metaData.' . JobMetaData::KEY . '.status')->in([
                    JobMetaData::STATUS_PENDING_ONLINE,
                    JobMetaData::STATUS_PENDING_OFFLINE,
                ])
                ->field('metaData.' . JobMetaData::KEY . '.uploadDate')->equals($uploadDate)
                ->getQuery()->execute();

            foreach ($jobs as $job) {
                $meta = JobMetaData::fromJob($job);
                $meta = $meta->receive()->storeIn($job);
                echo 'Job [ ' . $job->getId() . ' ] is ' . ($meta->isOnline() ? 'online' : 'offline') . PHP_EOL;
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
