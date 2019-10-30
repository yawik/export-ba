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

use ExportBA\Options\AaOptions;
use Interop\Container\ContainerInterface;

/**
 * Factory for \ExportBA\Controller\Plugin\JobFetcher
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobFetcherFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): JobFetcher {
        $aaOptions = $container->get(AaOptions::class);
        [$fetcherService, $fetcherOptions] = $aaOptions->getJobFetcher($options['name']);
        $fetcher = $container->build($fetcherService, $fetcherOptions);

        return new JobFetcher($fetcher);
    }
}
