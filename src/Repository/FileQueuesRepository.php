<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Repository;

use Core\Repository\AbstractRepository;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class FileQueuesRepository extends AbstractRepository
{
    public function findByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }
}
