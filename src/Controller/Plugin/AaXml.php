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

use ExportBA\Entity\FileQueue;
use ExportBA\Entity\JobMetaData;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Renderer\RendererInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaXml extends AbstractPlugin
{
    private $renderer;
    private $queue;
    private $supplierId;
    private $partnerNr;
    private $template;
    private $path;

    public function __construct(
        RendererInterface $renderer,
        FileQueue $queue,
        $supplierId,
        $partnerNr,
        $template,
        $path
    ) {
        $this->renderer = $renderer;
        $this->queue = $queue;
        $this->supplierId = $supplierId;
        $this->partnerNr = $partnerNr;
        $this->template = $template;
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
    }

    public function write($jobs)
    {
        //phpcs:ignore
        //$jobs = array_map(function ($x) { return new AaJob($x); }, $jobs);
        $processed = new \stdClass();
        $processed->count = 0;

        $content = $this->renderer->render(
            $this->template,
            [
                'supplierId' => $this->supplierId,
                'partnerNr' => $this->partnerNr,
                'jobs' => $jobs,
                'processed' => $processed,
            ]
        );

        if (!$processed->count) {
            return false;
        }

        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $uploadDate = date('Y-m-d_H-i-s');
        $file = $this->path . 'DS' . $this->supplierId . '_' . $uploadDate . '_D000E.XML';
        file_put_contents($file, $content);
        $this->queue->push($file);

        foreach ($jobs as $job) {
            JobMetaData::fromJob($job)->withUploadDate($uploadDate)->storeIn($job);
        }
        return [$file];
    }
}
