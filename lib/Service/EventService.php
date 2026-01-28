<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Service;

use OCA\ClubSuiteTraining\Db\EventMapper;
use OCA\ClubSuiteTraining\Db\EventEntity;

class EventService {
    private EventMapper $mapper;
    private TalkRoomService $talkRoomService;

    public function __construct(
        EventMapper $mapper,
        TalkRoomService $talkRoomService
    ) {
        $this->mapper = $mapper;
        $this->talkRoomService = $talkRoomService;
    }

    public function listEvents(): array {
        return $this->mapper->findAll();
    }

    public function getEvent(int $id): ?EventEntity {
        return $this->mapper->findById($id);
    }

    public function createEvent(
        int $groupId,
        string $date,
        string $startTime,
        string $endTime,
        ?string $location = null,
        ?string $notes = null,
        ?string $title = null
    ): EventEntity {
        $event = EventEntity::build([
            'groupId' => $groupId,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'location' => $location,
            'notes' => $notes,
            'title' => $title,
        ]);
        $created = $this->mapper->create($event);
        
        // Automatically create Talk room for the event
        $this->talkRoomService->createTalkRoomForEvent($created->getId());
        
        return $created;
    }

    public function updateEvent(int $id, array $data): ?EventEntity {
        $event = $this->mapper->find($id);
        if (!$event) {
            return null;
        }

        if (isset($data['date'])) {
            $event->setDate($data['date']);
        }
        if (isset($data['startTime'])) {
            $event->setStartTime($data['startTime']);
        }
        if (isset($data['endTime'])) {
            $event->setEndTime($data['endTime']);
        }
        if (isset($data['location'])) {
            $event->setLocation($data['location']);
        }
        if (isset($data['notes'])) {
            $event->setNotes($data['notes']);
        }
        if (isset($data['title'])) {
            $event->setTitle($data['title']);
        }

        $this->mapper->update($event);

        // Post update message to Talk room if it exists
        $talkRoom = $this->talkRoomService->getTalkRoomForEvent($id);
        if ($talkRoom) {
            $message = "📅 Event updated: {$event->getTitle()} on {$event->getDate()} at {$event->getStartTime()}";
            $this->talkRoomService->sendMessageToRoom($talkRoom->getRoomToken(), $message);
        }

        return $event;
    }
}
