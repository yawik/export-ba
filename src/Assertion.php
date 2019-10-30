<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA;

use Assert\Assertion as BaseAssertion;
use ExportBA\Exception\DomainException;
use ExportBA\Exception\InvalidArgumentException;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class Assertion extends BaseAssertion
{
    protected static $exceptionClass = InvalidArgumentException::class;

    public static function inArray($value, array $choices, $message = null, ?string $propertyPath = null): bool
    {
        try {
            return parent::inArray($value, $choices, $message, $propertyPath);
        } catch (InvalidArgumentException $e) {
            throw new DomainException($e);
        }
    }
}
