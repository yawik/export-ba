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


        $jobs = [];
        /** @var \Jobs\Entity\Job $job */
        foreach ($qb->getQuery()->execute() as $job) {
            /** @var JobMetaData $jobMeta */
            $jobMeta = $job->getAttachedEntity(JobMetaData::class);

            if ($job->isActive() && !$jobMeta) {
                $jobs[] = $job;
                continue;
            }

            if (
                !$jobMeta
                || $jobMeta->hasStatus(JobMetaStatus::PENDING_OFFLINE)
                || $jobMeta->hasStatus(JobMetaStatus::PENDING_ONLINE)
                || ($jobMeta->hasStatus(JobMetaStatus::OFFLINE) && !$job->isActive())
                || ($jobMeta->hasStatus(JobMetaStatus::ONLINE) && $job->isActive())
            ) {
                continue;
            }

            $jobs[] = $job;
        }

        $this->repository->getDocumentManager()->clear();
        return $jobs;
    }
}
