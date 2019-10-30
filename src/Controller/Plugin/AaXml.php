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
    private $supplierId;
    private $partnerNr;
    private $template;
    private $path;

    public function __construct(RendererInterface $renderer, $supplierId, $partnerNr, $template, $path)
    {
        $this->renderer = $renderer;
        $this->supplierId = $supplierId;
        $this->partnerNr = $partnerNr;
        $this->template = $template;
        $this->path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
    }

    public function write($jobs)
    {
        //phpcs:ignore
        //$jobs = array_map(function ($x) { return new AaJob($x); }, $jobs);
        $processed = new \stdClass;
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

        $file = $this->path . 'DS' . $this->supplierId . '_' . date('Y-m-d_H-i-s') . '_D001.XML';
        file_put_contents($file, $content);

        return [$file];
    }
}
