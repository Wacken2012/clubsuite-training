<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\AppFramework\Db\Entity;
use DateTime;
use JsonSerializable;

class EventEntity extends Entity implements JsonSerializable {
    protected int $groupId;
    protected string $date;
    protected string $startTime;
    protected string $endTime;
    protected ?string $location = null;
    protected ?string $notes = null;
    protected ?string $title = null;

    public function __construct() {
        $this->addType('groupId', 'integer');
    }

    public static function build(array $params): static {
        $e = new static();
        if (isset($params['groupId'])) {
            $e->setGroupId($params['groupId']);
        }
        if (isset($params['date'])) {
            $e->setDate($params['date']);
        }
        if (isset($params['startTime'])) {
            $e->setStartTime($params['startTime']);
        }
        if (isset($params['endTime'])) {
            $e->setEndTime($params['endTime']);
        }
        if (isset($params['location'])) {
            $e->setLocation($params['location']);
        }
        if (isset($params['notes'])) {
            $e->setNotes($params['notes']);
        }
        if (isset($params['title'])) {
            $e->setTitle($params['title']);
        }
        return $e;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'group_id' => $this->groupId,
            'title' => $this->title,
            'date' => $this->date,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'location' => $this->location,
            'notes' => $this->notes
        ];
    }

    public function getGroupId(): int { return $this->groupId; }
    public function setGroupId(int $groupId): void { $this->groupId = $groupId; }
    public function getDate(): string { return $this->date; }
    public function setDate(string $date): void { $this->date = $date; }
    public function getStartTime(): string { return $this->startTime; }
    public function setStartTime(string $startTime): void { $this->startTime = $startTime; }
    public function getEndTime(): string { return $this->endTime; }
    public function setEndTime(string $endTime): void { $this->endTime = $endTime; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $l): void { $this->location = $l; }
    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $n): void { $this->notes = $n; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $t): void { $this->title = $t; }
}
