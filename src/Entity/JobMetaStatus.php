<?php

/**
 * YAWIK Export BA
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace ExportBA\Entity;

use Core\Entity\Status\AbstractStatus;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * TODO: description
 * @ODM\EmbeddedDocument
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobMetaStatus extends AbstractStatus
{
    public const NEW = 'new';
    public const PENDING_ONLINE = 'pending-online';
    public const ONLINE = 'online';
    public const PENDING_OFFLINE = 'pending-offline';
    public const OFFLINE = 'offline';
    public const ERROR = 'error';
}
