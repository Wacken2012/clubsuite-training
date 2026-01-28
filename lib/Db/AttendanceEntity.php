<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\AppFramework\Db\Entity;
use JsonSerializable;

class AttendanceEntity extends Entity implements JsonSerializable {
    protected int $eventId;
    protected string $userId;
    protected string $status = 'absent';
    protected ?int $memberId = null;
    protected ?\DateTime $checkedInAt = null;
    protected ?\DateTime $checkedOutAt = null;

    public function __construct() {
        $this->addType('eventId', 'integer');
        $this->addType('memberId', 'integer');
        $this->addType('checkedInAt', 'datetime');
        $this->addType('checkedOutAt', 'datetime');
    }

    public static function build(array $params): static {
        $e = new static();
        if (isset($params['eventId'])) {
            $e->setEventId($params['eventId']);
        }
        if (isset($params['userId'])) {
            $e->setUserId($params['userId']);
        }
        if (isset($params['status'])) {
            $e->setStatus($params['status']);
        }
        if (isset($params['memberId'])) {
            $e->setMemberId($params['memberId']);
        }
        if (isset($params['checkedInAt'])) {
            $e->setCheckedInAt($params['checkedInAt']);
        }
        if (isset($params['checkedOutAt'])) {
            $e->setCheckedOutAt($params['checkedOutAt']);
        }
        return $e;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'user_id' => $this->userId,
            'member_id' => $this->memberId,
            'status' => $this->status,
            'checked_in_at' => $this->checkedInAt?->format('c'),
            'checked_out_at' => $this->checkedOutAt?->format('c'),
        ];
    }

    public function getEventId(): int { return $this->eventId; }
    public function setEventId(int $eventId): void { $this->eventId = $eventId; }
    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $userId): void { $this->userId = $userId; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $s): void { $this->status = $s; }
    public function getMemberId(): ?int { return $this->memberId; }
    public function setMemberId(?int $m): void { $this->memberId = $m; }
    public function getCheckedInAt(): ?\DateTime { return $this->checkedInAt; }
    public function setCheckedInAt(?\DateTime $t): void { $this->checkedInAt = $t; }
    public function getCheckedOutAt(): ?\DateTime { return $this->checkedOutAt; }
    public function setCheckedOutAt(?\DateTime $t): void { $this->checkedOutAt = $t; }
}
