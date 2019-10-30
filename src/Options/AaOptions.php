<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class AaOptions extends AbstractOptions
{
    private $configurations;

    /**
     * @var string
     */
    private $certPath = 'var/cache/export-ba/cert';
    /**
     * @var string
     */
    private $cachePath = 'var/cache/export-ba/cache';


    /**
     * Set configurations
     *
     * @param array $configurations
     */
    public function setConfigurations($configurations): void
    {
        $this->configurations = $configurations;
    }

    public function getJobFetcher(string $name)
    {
        return $this->configurations[$name]['fetcher'] ?? ['defaultFetcher', []];
    }

    /**
     * Get certPath
     *
     * @return string
     */
    public function getCertPath(): string
    {
        return $this->certPath;
    }

    /**
     * Set certPath
     *
     * @param string $certPath
     */
    public function setCertPath(string $certPath): void
    {
        $this->certPath = $certPath;
    }

    /**
     * Get cachePath
     *
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Set cachePath
     *
     * @param string $cachePath
     */
    public function setCachePath(string $cachePath): void
    {
        $this->cachePath = $cachePath;
    }

    public function getTemplate(string $name)
    {
        return $this->configurations[$name]['template'] ?? 'export-ba/default';
    }

    public function getSupplierId(string $name)
    {
        return $this->configurations[$name]['supplier_id'] ?? null;
    }

    public function getPartnerNr(string $name)
    {
        return $this->configurations[$name]['partner_nr'] ?? null;
    }
}
