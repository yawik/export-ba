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

use ExportBA\Client\AaClient;
use ExportBA\Controller\Plugin\AaXml;
use ExportBA\Controller\Plugin\JobFetcher;
use Zend\Mvc\Console\Controller\AbstractConsoleController;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class UploadConsoleController extends AbstractConsoleController
{
    private $client;

    public static function usage(): array
    {

    }

    public function __construct(AaClient $client)
    {
        $this->client = $client;
    }

    public function indexAction()
    {
        $name = $this->params('name');

        $jobs = $this->plugin(JobFetcher::class, ['name' => $name])->fetch();

        if (!count($jobs)) {
            echo "No jobs found." . PHP_EOL;
            return;
        }
        echo count($jobs) . 'found.' . PHP_EOL;

        $files = $this->plugin(AaXml::class, ['name' => $name])->write($jobs);

        if ($files === false) {
            echo "No jobs processed. All fine." . PHP_EOL;
            return;
        }

        $this->client->open($name);

        echo "Uploading..." . PHP_EOL;
        foreach ($files as $file) {
            $this->client->upload($file);
        }

        echo 'Done.' . PHP_EOL;
    }
}
