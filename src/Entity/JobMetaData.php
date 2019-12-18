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

use Core\Entity\IdentifiableEntityInterface;
use Core\Entity\IdentifiableEntityTrait;
use Core\Entity\ModificationDateAwareEntityInterface;
use Core\Entity\ModificationDateAwareEntityTrait;
use Core\Entity\Status\StatusAwareEntityInterface;
use Core\Entity\Status\StatusAwareEntityTrait;
use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 *
 * @ODM\Document(collection="exportBA.jobmeta", repositoryClass="ExportBA\Repository\JobMetaRepository)
 * @ODM\HasLifecyclaCallbacks
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobMetaData implements
    StatusAwareEntityInterface,
    IdentifiableEntityInterface,
    ModificationDateAwareEntityInterface
{
    use StatusAwareEntityTrait;
    use IdentifiableEntityTrait;
    use ModificationDateAwareEntityTrait;

    public const STATUS_ENTITY_CLASS = JobMetaStatus::class;

    /**
     * @ODM\Collection
     * @var array
     */
    private $messages = [];

    /**
     * @ODM\Field(type="string")
     * @var string
     */
    private $jobId;

    public function setJobId(string $id)
    {
        $this->jobId = $id;
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    public function mustProcess(): bool
    {
        return
            !$this->hasStatus(JobMetaStatus::PENDING_OFFLINE)
            && !$this->hasStatus(JobMetaStatus::PENDING_ONLINE)
            && !$this->hasStatus(JobMetaStatus::ERROR)
        ;
    }

    public function commit(?string $message = 'Send to BA.'): self
    {
        $status =
            $this->hasStatus(JobMetaStatus::NEW)
            || $this->hasStatus(JobMetaStatus::ONLINE)
            || $this->hasStatus(JobMetaStatus::ERROR)
            ? JobMetaStatus::PENDING_ONLINE
            : JobMetaStatus::PENDING_OFFLINE
        ;

        return $this->updateStatus($status, $message);
    }

    public function receive(?string $message = null): self
    {
        if ($this->hasStatus(JobMetaStatus::PENDING_ONLINE)) {
            return $this->updateStatus(
                JobMetaStatus::ONLINE,
                $message ?? 'Successfully received by BA. now online.'
            );
        }

        return $this->updateStatus(
            JobMetaStatus::OFFLINE,
            $message ?? 'Successfully deleted by BA. now offline.'
        );
    }

    public function error(string $message): self
    {
        return $this->updateStatus(JobMetaStatus::ERROR, $message);
    }

    public function updateStatus(string $status, string $message): self
    {
        $originalStatus = (string) $this->getStatus();

        $this->setStatus($status);
        $this->messages[] = sprintf(
            '%s: [%s -> %s] %s',
            (new DateTime())->format('Y-m-d H:i:s T(O)'),
            $originalStatus,
            (string) $status,
            $message
        );

        return $this;
    }
}
