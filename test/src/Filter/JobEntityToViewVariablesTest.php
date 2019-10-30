<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBATest\Filter;

use PHPUnit\Framework\TestCase;
use ExportBA\Filter\JobEntityToViewVariables;
use Jobs\Entity\Job;
use Jobs\Entity\StatusInterface;

/**
 * Testcase for \ExportBA\Filter\JobEntityToViewVariables
 *
 * @covers \ExportBA\Filter\JobEntityToViewVariables
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group
 */
class JobEntityToViewVariablesTest extends TestCase
{
    public function testAssertions()
    {
        $target = new JobEntityToViewVariables();
        $entity = new Job();
        $entity->setTitle('TestJobTitle');
        $entity->setStatus(StatusInterface::REJECTED);

        $data = $target->filter($entity);

        var_dump($data);
        static::assertArrayHasKey('title', $data);
        static::assertEquals('TestJobTitle', $data['title']);
    }
}
