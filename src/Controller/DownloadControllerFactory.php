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

use Interop\Container\ContainerInterface;

/**
 * Factory for \ExportBA\Controller\DownloadController
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class DownloadControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        ?string $requestedName = null,
        ?array $options = null
    ): DownloadController {
        return new DownloadController();
    }
}
