<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Controller;

use ExportBA\Controller\Plugin\AaResponseProcessor;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class DownloadController extends AbstractConsoleController
{
    public function indexAction()
    {
        $name = $this->params('name');
        $processor = $this->plugin(AaResponseProcessor::class, ['name' => $name]);
        $processor->process();
    }
}
