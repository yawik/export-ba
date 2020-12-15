<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright (c) 2019 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    Mathias Gelhausen <gelhausen@cross-solution.de>
 */

declare(strict_types=1);

namespace ExportBA;

use Core\ModuleManager\Feature\VersionProviderInterface;
use Core\ModuleManager\Feature\VersionProviderTrait;
use Core\ModuleManager\ModuleConfigLoader;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\ModuleManager\Feature\DependencyIndicatorInterface;

/**
 * Bootstrap module
 */
class Module implements
    DependencyIndicatorInterface,
    ConsoleBannerProviderInterface,
    ConsoleUsageProviderInterface,
    VersionProviderInterface
{
    use VersionProviderTrait;

    public const VERSION = '0.2.1';

    /**
     * Loads module specific configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return ModuleConfigLoader::load(__DIR__ . '/../config');
    }

    /**
     * {@inheritDoc}
     * @see DependencyIndicatorInterface::getModuleDependencies()
     */
    public function getModuleDependencies()
    {
        return ['Jobs'];
    }

    public function getConsoleBanner(Console $console)
    {
        return sprintf("%s (%s) %s", __NAMESPACE__, $this->getName(), $this->getVersion());
    }


    /**
     * {@inheritDoc}
     * @see ConsoleUsageProviderInterface::getConsoleUsage()
     */
    public function getConsoleUsage(Console $console)
    {
        return [];
    }
}
