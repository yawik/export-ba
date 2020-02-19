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

use Laminas\Stdlib\AbstractOptions;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class ClientOptions extends AbstractOptions
{
    /**
     * @var array
     */
    private $connections;


    /**
     * Set connections
     *
     * @param array $connections
     */
    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
    }

    public function get($name)
    {
        if (!isset($this->connections[$name])) {
            return null;
        }

        return new ConnectionOptions($this->connections[$name]);
    }
}
