<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);
namespace ExportBATest\Entity;

use ExportBA\Assert;
use PHPUnit\Framework\TestCase;
use ExportBA\Entity\JobMetaData;
use Jobs\Entity\Job;

/**
 * Testcase for \ExportBA\Entity\JobMetaData
 *
 * @covers \ExportBA\Entity\JobMetaData
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group
 */
class JobMetaDataTest extends TestCase
{

    public function testValidation()
    {
        Assert::allNullOrString(['Onkel', 1.45], 'domain');
        //Assert::$enabled = false;
        date_default_timezone_set('Europe/Berlin');
        $entity = new Job();
        $entity->setMetaData('exportBA', ['status' => 'onlinse']);
        $data = JobMetaData::fromJob($entity);
        $some = $data
            ->commit();

        $some->receive()
            ->commit('Update company name.')
            ->error('Invalid companyname')
            ->storeIn($entity)
        ;
        var_dump($entity->getMetaData('exportBA'));
        static::assertSame($data, $some);
    }
}
