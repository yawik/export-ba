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
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;

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

    public const VERSION = '0.0.0';

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
