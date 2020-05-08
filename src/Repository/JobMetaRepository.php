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

use Core\Entity\IdentifiableEntityInterface;
use Core\Repository\AbstractRepository;
use ExportBA\Entity\JobMetaStatus;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobMetaRepository extends AbstractRepository
{

    public function getMetaDataFor($job)
    {
        if ($job instanceof IdentifiableEntityInterface) {
            $job = $job->getId();
        }

        if (!is_string($job)) {
            throw new \InvalidArgumentException('Job Id must be string or IdentifiableEntityInterface');
        }

        $entity = $this->findOneBy(['jobId' => $job]);

        if (!$entity) {
            $entity = $this->create(['jobId' => $job, 'status' => JobMetaStatus::NEW], true);
        }

        return $entity;
    }
}
