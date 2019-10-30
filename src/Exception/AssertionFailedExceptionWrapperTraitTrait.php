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

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
trait AssertionFailedExceptionWrapperTraitTrait
{
    private $assertionFailedException;

    public function __construct(AssertionFailedException $e)
    {
        parent::__construct($e->getMessage(), $e->getCode(), $e->getPrevious());
        $this->assertionFailedException = $e;
    }

    public function getOriginalException()
    {
        return $this->assertionFailedException;
    }

    /**
     * User controlled way to define a sub-property causing
     * the failure of a currently asserted objects.
     *
     * Useful to transport information about the nature of the error
     * back to higher layers.
     *
     * @return string|null
     */
    public function getPropertyPath()
    {
        return $this->assertionFailedException->getPropertyPath();
    }

    /**
     * Get the value that caused the assertion to fail.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->assertionFailedException->getValue();
    }

    /**
     * Get the constraints that applied to the failed assertion.
     *
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->assertionFailedException->getConstraints();
    }
}
