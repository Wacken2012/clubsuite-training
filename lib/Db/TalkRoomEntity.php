<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\AppFramework\Db\Entity;
use DateTime;
use JsonSerializable;

class TalkRoomEntity extends Entity implements JsonSerializable {
    protected int $eventId;
    protected string $roomToken;
    protected ?string $roomUrl = null;
    protected ?DateTime $createdAt = null;

    public function __construct() {
        $this->addType('eventId', 'integer');
    }

    public static function build(array $params): static {
        $e = new static();
        if (isset($params['eventId'])) {
            $e->setEventId($params['eventId']);
        }
        if (isset($params['roomToken'])) {
            $e->setRoomToken($params['roomToken']);
        }
        if (isset($params['roomUrl'])) {
            $e->setRoomUrl($params['roomUrl']);
        }
        if (isset($params['createdAt'])) {
            $e->setCreatedAt($params['createdAt']);
        }
        return $e;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'event_id' => $this->eventId,
            'room_token' => $this->roomToken,
            'room_url' => $this->roomUrl,
            'created_at' => $this->createdAt ? $this->createdAt->format('c') : null,
        ];
    }

    public function getEventId(): int { return $this->eventId; }
    public function setEventId(int $eventId): void { $this->eventId = $eventId; }
    
    public function getRoomToken(): string { return $this->roomToken; }
    public function setRoomToken(string $token): void { $this->roomToken = $token; }
    
    public function getRoomUrl(): ?string { return $this->roomUrl; }
    public function setRoomUrl(?string $url): void { $this->roomUrl = $url; }
    
    public function getCreatedAt(): ?DateTime { return $this->createdAt; }
    public function setCreatedAt(?DateTime $dt): void { $this->createdAt = $dt; }
}
