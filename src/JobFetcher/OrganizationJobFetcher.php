<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\JobFetcher;

use ExportBA\Entity\JobMetaData;
use Jobs\Entity\StatusInterface;
use Jobs\Repository\Job;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class OrganizationJobFetcher implements JobFetcherInterface
{
    private $repository;
    private $id;

    public function __construct(Job $repository, $options)
    {
        $this->repository = $repository;
        $this->id = $options['id'] ?? null;
    }

    public function fetch(): iterable
    {
        $qb = $this->repository->createQueryBuilder();
        $qb->field('organization')->equals(new \MongoId($this->id));
        $qb->field('status.name')->in([
            StatusInterface::ACTIVE,
            StatusInterface::EXPIRED,
            StatusInterface::INACTIVE,
        ]);
        $qb->addOr(
            $qb->expr()->field('metaData.exportBA')->exists(false),
            $qb->expr()->field('metaData.exportBA.status')->notIn([
                JobMetaData::STATUS_ERROR,
                JobMetaData::STATUS_PENDING_OFFLINE,
                JobMetaData::STATUS_PENDING_ONLINE,
            ])
        );

        return $qb->getQuery()->execute();
    }
}
