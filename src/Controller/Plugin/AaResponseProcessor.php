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
use ExportBA\Repository\JobMetaRepository;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaResponseProcessor
{
    private $client;
    private $queue;
    private $name;
    private $supplierId;
    private $files = [];
    /**
     * @var JobMetaRepository
     */
    private $metaData;

    public function __construct(AaClient $client, $queue, $name, $supplierId, $metaData)
    {
        $this->client = $client;
        $this->queue = $queue;
        $this->supplierId = $supplierId;
        $this->name = $name;
        $this->metaData = $metaData;
        $this->init();
    }

    private function init()
    {
        $this->client->open($this->name);

        $html = $this->client->get();

        $dom = new \DomDocument("1.0", 'UTF-8');
        $dom->loadHTML($html['html']);

        foreach ($dom->getElementsByTagName('a') as $node) {
            /** @var \DOMElement $node */
            if (!$node->hasAttribute('href')) {
                continue;
            }
            $href = $node->getAttribute('href');
            if (strpos($href, 'ESP' . $this->supplierId . '_') !== 0) {
                continue;
            }

            $this->files[] = $href;
        }
    }

    public function process()
    {
        while ($file = $this->queue->pop()) {
            echo "Process $file:\n";
            if (($responseFiles = $this->getResponseFiles($file)) === false) {
                echo "No response files found.\n";
                $this->queue->push($file);
                continue;
            }

            foreach ($responseFiles as $responseFile) {
                echo "-- Response file: $responseFile \n";
                $responseFile = $this->client->download($responseFile);
                if (!$responseFile) { continue; }

                $content = file_get_contents($responseFile);

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

                    /** @var JobMetaData $job */
                    $job = $this->metaData->findOneBy(['jobId' => $id]);
                    if (!$job) {
                        echo 'No job data for id ', $id, PHP_EOL;
                        continue;
                    }

                    if ($errXml->ErrorCode == 'FLR_DataStore_130') {
                        if ($job->hasStatus(JobMetaStatus::PENDING_OFFLINE)) {
                            echo "Job [ $id ] already deleted. update status to OFFLINE.\n";
                            $job->updateStatus(JobMetaStatus::OFFLINE, 'Was already offline on AA.');
                        } elseif ($job->hasStatus(JobMetaStatus::PENDING_ONLINE)) {
                            echo "Job [ $id ] does not exist on AA. Remove this meta data to treat as new.\n";
                            $this->metaData->remove($job);
                        }
                    }

                    $job->error($error);
                    echo 'Job [' . $id . '] has errors. Set status to ERROR' . PHP_EOL;
                }
            }

            $this->metaData->getDocumentManager()->flush();

            echo "-- Process valid jobs\n";
            $jobs = $this->metaData->findBy(['status.name' => JobMetaStatus::PENDING_ONLINE]);
            foreach ($jobs as $job) {
                /** @var \ExportBA\Entity\JobMetaData $job */
                echo "Job {$job->getJobId()} is online.\n";
                $job->receive();
            }
        }
    }

    private function getResponseFiles($file)
    {
        $files = [];
        [0 => $prefix, 1 => $date] = explode('_', $file);
        $prefix = str_replace('DS', 'ESP', $prefix) . '_' . $date;

        foreach ($this->files as $responseFile) {
            if (strpos($responseFile, $prefix) === 0) {
                echo "Found response file: $responseFile\n";
                $files[] = $responseFile;
            }
        }

        return $files ?: false;
    }
}
