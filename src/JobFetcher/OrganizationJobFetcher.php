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
use ExportBA\Entity\JobMetaStatus;
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
        $qb->addAnd(
            $qb->expr()->addOr([
                $qb->expr()->field('status.metaData.' . JobMetaData::KEY)->exists(false),
                $qb->expr()->field('status.metaData.' . JobMetaData::KEY . '.status')->in([
                    JobMetaData::STATUS_NEW,
                    JobMetaData::STATUS_ONLINE,
                    JobMetaData::STATUS_OFFLINE,
                ])
            ])
        );


        $jobs = [];
        /** @var \Jobs\Entity\Job $job */
        foreach ($qb->getQuery()->execute() as $job) {
            /** @var JobMetaData $jobMeta */
            $jobMeta = JobMetaData::fromJob($job);

            if ($job->isActive() && $jobMeta->isNew()) {
                $jobs[] = $job;
                continue;
            }

            if (
                $jobMeta->isNew()
                || ($jobMeta->isOffline() && !$job->isActive())
                || ($jobMeta->isOnline() && $job->isActive() && $job->getDateModified() <= $jobMeta->lastStatusDate())
            ) {
                continue;
            }

            $jobs[] = $job;
        }

        return $jobs;
    }
}
