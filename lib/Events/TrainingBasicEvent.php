<?php
namespace OCA\ClubSuiteTraining\Events;

use OCP\EventDispatcher\Event;

class TrainingBasicEvent extends Event {
    private string $id;
    private int $timestamp;
    private array $payload;

    public function __construct(string $id, int $timestamp, array $payload = []) {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->payload = $payload;
    }

    public function getId(): string { return $this->id; }
    public function getTimestamp(): int { return $this->timestamp; }
    public function getPayload(): array { return $this->payload; }
}
