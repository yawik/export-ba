<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\JobFetcher;

use Interop\Container\ContainerInterface;

/**
 * Factory for \ExportBA\JobFetcher\OrganizationJobFetcher
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class OrganizationJobFetcherFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): OrganizationJobFetcher {
        return new OrganizationJobFetcher(
            $container->get('repositories')->get('Jobs'),
            $options
        );
    }
}
