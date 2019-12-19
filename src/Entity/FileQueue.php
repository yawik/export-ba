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

use Core\Entity\EntityInterface;
use Core\Entity\EntityTrait;
use Core\Entity\IdentifiableEntityInterface;
use Core\Entity\IdentifiableEntityTrait;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * TODO: description
 * @ODM\Document(collection="exportBA.filequeues", repositoryClass="ExportBA\Repository\FileQueuesRepository")
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class FileQueue implements EntityInterface, IdentifiableEntityInterface
{
    use EntityTrait;
    use IdentifiableEntityTrait;

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    protected $name;

    /**
     * @ODM\Field(type="collection")
     * @var array
     */
    protected $files = [];

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function push($file)
    {
        array_unshift($this->files, $file);
    }

    public function current()
    {
        return end($this->files);
    }

    public function pop()
    {
        return array_pop($this->files);
    }
}
