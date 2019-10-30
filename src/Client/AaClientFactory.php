<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Client;

use ExportBA\Options\AaOptions;
use Interop\Container\ContainerInterface;

/**
 * Factory for \ExportBA\Client\AaClient
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaClientFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): AaClient {
        $options = $container->get(AaOptions::class);
        return new AaClient(
            $options->getCertPath(),
            $options->getCachePath()
        );
    }
}
