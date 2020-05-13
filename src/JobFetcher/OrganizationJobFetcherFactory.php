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

use ExportBA\Entity\JobMetaData;
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
        $repos = $container->get('repositories');

        return new OrganizationJobFetcher(
            $repos->get('Jobs'),
            $repos->get(JobMetaData::class),
            $options
        );
    }
}
