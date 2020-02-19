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

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobFetcher extends AbstractPlugin
{
    private $fetcher;

    public function __construct($fetcher)
    {
        $this->fetcher = $fetcher;
    }

    public function fetch()
    {
        return $this->fetcher->fetch();
    }
}
