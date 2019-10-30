<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\ViewHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class TitleCode extends AbstractHelper
{
    private $map = [];

    public function __invoke(string $title)
    {
        return $this->map[$title] ?? 1234;
    }
}
