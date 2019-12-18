<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Entity;

use SimpleImport\Entity\JobMetaData as SiData;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class SimpleImportMetaDataProxy
{
    /**
     * @var SiData|null
     */
    private $data;

    public function __construct($job)
    {
        //phpcs:ignored
        $this->data = $job->getAttachedEntity(SiData::class);
    }

    public function getData()
    {
        return $this->data ? $this->data->getData() : [];
    }

    public function has(string $key): bool
    {
        return $this->data ? $this->data->has($key) : false;
    }

    public function get(string $key, $default = null)
    {
        return $this->data ? $this->data->get($key, $default) : $default;
    }
}
