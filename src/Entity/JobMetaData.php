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

use DateTime;
use ExportBA\Assert;
use Jobs\Entity\JobInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobMetaData
{
    public const KEY = 'exportBA';
    public const STATUS_NEW = 'new';
    public const STATUS_PENDING_ONLINE = 'pending-online';
    public const STATUS_ONLINE = 'online';
    public const STATUS_PENDING_OFFLINE = 'pending-offline';
    public const STATUS_OFFLINE = 'offline';
    public const STATUS_ERROR = 'error';

    private $uploadDate;
    private $status = self::STATUS_NEW;
    private $messages = [];

    public static function fromJob(JobInterface $job)
    {
        $data = $job->getMetaData(static::KEY) ?? [];

        return new self(
            $data['status'] ?? self::STATUS_NEW,
            $data['messages'] ?? [],
            $data['uploadDate'] ?? null
        );
    }

    private function __construct(string $status, array $messages = [], ?string $uploadDate = null)
    {
        Assert::that(null)->nullOrString()->oneOf([
                self::STATUS_NEW,
                self::STATUS_PENDING_ONLINE,
                self::STATUS_ONLINE,
                self::STATUS_PENDING_OFFLINE,
                self::STATUS_OFFLINE,
                self::STATUS_ERROR
        ]);

        $this->status = $status;
        $this->messages = $messages;
        $this->uploadDate = $uploadDate;
    }

    public function withUploadDate(string $date): self
    {
        $data = clone $this;
        $data->uploadDate = $date;

        return $data;
    }

    public function isNew(): bool
    {
        return $this->status == self::STATUS_NEW;
    }

    public function isOnline(): bool
    {
        return $this->status == self::STATUS_ONLINE;
    }

    public function isOffline(): bool
    {
        return $this->status == self::STATUS_OFFLINE;
    }

    public function isPendingOnline(): bool
    {
        return $this->status == self::STATUS_PENDING_ONLINE;
    }

    public function isPendingOffline(): bool
    {
        return $this->status == self::STATUS_PENDING_OFFLINE;
    }

    public function mustProcess(): bool
    {
        return
            !$this->isPendingOffline()
            && !$this->isPendingOnline()
            && !$this->isError()
        ;
    }

    public function isError(): bool
    {
        return $this->status == self::STATUS_ERROR;
    }

    public function commit(?string $message = 'Send to BA.'): self
    {
        $status =
            $this->isNew() || $this->isOnline() || $this->isError()
            ? self::STATUS_PENDING_ONLINE
            : self::STATUS_PENDING_OFFLINE
        ;

        return $this->withStatus($status, $message);
    }

    public function receive(?string $message = null): self
    {
        if ($this->isPendingOnline()) {
            return $this->withStatus(
                self::STATUS_ONLINE,
                $message ?? 'Successfully received by BA. now online.'
            );
        }

        return $this->withStatus(
            self::STATUS_OFFLINE,
            $message ?? 'Successfully deleted by BA. now offline.'
        );
    }

    public function error(string $message): self
    {
        return $this->withStatus(self::STATUS_ERROR, $message);
    }

    public function storeIn(JobInterface $job): void
    {
        $job->setMetaData(
            static::KEY,
            [
                'status' => $this->status,
                'messages' => $this->messages,
                'uploadDate' => $this->uploadDate,
            ]
        );
    }

    public function lastStatusDate()
    {
        $status = array_pop($this->messages);

        if (!$status) {
            return new DateTime();
        }

        return new DateTime($status[0]);
    }

    public function withStatus(string $status, string $message): self
    {
        $originalStatus = $this->status;
        $data = clone $this;
        $data->status = $status;
        $data->messages[] = [
            (new DateTime())->format('c'),
            sprintf(
                '[%s -> %s] %s',
                $originalStatus,
                $status,
                $message
            )
        ];

        return $data;
    }
}
