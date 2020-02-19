<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Filter;

use ExportBA\Assert;
use ExportBA\Entity\JobMetaData;
use Jobs\Entity\JobInterface;
use Jobs\Entity\StatusInterface;
use Laminas\Filter\FilterInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobEntityToViewVariables implements FilterInterface
{

    public function filter($value)
    {
        Assert::that($value)->isObject()->isInstanceOf(JobInterface::class);

        return array_merge(
            $this->determineAction($value),
            $this->extractJobTitle($value),
            $this->extractLocations($value)
        );
    }

    private function extractJobTitle($job): array
    {
        return [
            'title' => $job->getTitle(),
            'titleCode' => '0000',
        ];
    }

    private function extractLocations($job): array
    {
        $locations = $job->getLocations();
        $result = [];

        /** @var \Jobs\Entity\Location $loc */
        foreach ($locations as $loc) {
            $result[] = [
                'country' => $loc->getCountry(),
                'postalcode' => $loc->getPostalCode(),
                'city' => $loc->getCity(),
            ];
        }

        return ['locations' => $result];
    }

    private function determineAction(JobInterface $job): array
    {
        if ($job->getStatus()->is(StatusInterface::ACTIVE)) {
            $data = JobMetaData::fromJob($job);
            return ['action' => $data->isNew() ? 'insert' : 'update'];
        }

        return ['action' => 'delete'];
    }
}
