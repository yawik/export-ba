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
use ExportBA\Entity\FileQueue;
use ExportBA\Entity\JobMetaData;
use ExportBA\Options\AaOptions;
use Interop\Container\ContainerInterface;

/**
 * Factory for \ExportBA\Controller\Plugin\AaResponseProcessor
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaResponseProcessorFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): AaResponseProcessor {
        $aaOptions = $container->get(AaOptions::class);

        /** @var \ExportBA\Repository\FileQueuesRepository $queues */
        $queues = $container->get('repositories')->get(FileQueue::class);
        $queue = $queues->findByName($options['name']);
        if (!$queue) {
            $queue = $queues->create(['name' => $options['name']], true);
        }
        return new AaResponseProcessor(
            $container->get(AaClient::class),
            $queue,
            $options['name'],
            $aaOptions->getSupplierId($options['name']),
            $container->get('repositories')->get(JobMetaData::class)
        );
    }
}
