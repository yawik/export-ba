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

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobId
{

    public static function toAaId(string $jobId, string $partnerNr): string
    {
        return $partnerNr . '-' . base64_encode(hex2bin($jobId)) . '-S';
    }

    public static function fromAaId(string $aaId): string
    {
        [1 => $id] = explode('-', $aaId);
        return bin2hex(base64_decode($id));
    }
}
