<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Exception;

use Assert\AssertionFailedException;
use DomainException as SplDomainException;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class DomainException extends SplDomainException implements ExceptionInterface, AssertionFailedException
{
    use AssertionFailedExceptionWrapperTraitTrait;
}
