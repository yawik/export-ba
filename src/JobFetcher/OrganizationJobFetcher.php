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
use ExportBA\Repository\JobMetaRepository;
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
    /**
     * @var JobMetaRepository
     */
    private $metaRepository;
    private $id;

    public function __construct(Job $repository, JobMetaRepository $jobMeta, $options)
    {
        $this->repository = $repository;
        $this->metaRepository = $jobMeta;
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
        // $qb->addAnd(
        //     $qb->expr()
        //     ->addOr($qb->expr()->field('status.metaData.' . JobMetaData::KEY)->exists(false))
        //     ->addOr(
        //         $qb->expr()->field('status.metaData.' . JobMetaData::KEY . '.status')->in([
        //             JobMetaData::STATUS_NEW,
        //             JobMetaData::STATUS_ONLINE,
        //             JobMetaData::STATUS_OFFLINE,
        //         ])
        //     )
        // );


        $jobs = [];
        /** @var \Jobs\Entity\Job $job */
        foreach ($qb->getQuery()->execute() as $job) {
            /** @var JobMetaData $jobMeta */
            $jobMeta = $this->metaRepository->getMetaDataFor($job);

            if (
                !$jobMeta->hasStatus(JobMetaStatus::NEW)
                && !$jobMeta->hasStatus(JobMetaStatus::ONLINE)
                && !$jobMeta->hasStatus(JobMetaStatus::OFFLINE)
            ) {
                continue;
            }

            if ($job->isActive() && $jobMeta->hasStatus(JobMetaStatus::NEW)) {
                $jobs[] = $job;
                continue;
            }

            if (
                $jobMeta->hasStatus(JobMetaStatus::NEW)
                || ($jobMeta->hasStatus(JobMetaStatus::OFFLINE) && !$job->isActive())
                || (
                    $jobMeta->hasStatus(JobMetaStatus::ONLINE)
                    && $job->isActive() && $job->getDateModified() <= $jobMeta->getDateModified()
                    && !$jobMeta->mustUpdate()
                )
            ) {
                continue;
            }

            $jobs[] = $job;
        }

        return $jobs;
    }
}
