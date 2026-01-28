<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class TalkRoomMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'clubsuite_training_talk_rooms', TalkRoomEntity::class);
    }

    /**
     * Find Talk room by event ID
     */
    public function findByEvent(int $eventId): ?TalkRoomEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId, IQueryBuilder::PARAM_INT)));
        
        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();
        
        if ($row === false) {
            return null;
        }
        
        return $this->mapRowToEntity($row);
    }

    /**
     * Find Talk room by ID
     */
    public function findById(int $id): ?TalkRoomEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
        
        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();
        
        if ($row === false) {
            return null;
        }
        
        return $this->mapRowToEntity($row);
    }

    /**
     * Find Talk room by token
     */
    public function findByToken(string $token): ?TalkRoomEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->getTableName())
            ->where($qb->expr()->eq('room_token', $qb->createNamedParameter($token)));
        
        $result = $qb->executeQuery();
        $row = $result->fetch();
        $result->closeCursor();
        
        if ($row === false) {
            return null;
        }
        
        return $this->mapRowToEntity($row);
    }

    /**
     * Update Talk room
     */
    public function update(TalkRoomEntity $entity): TalkRoomEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
            ->set('event_id', $qb->createNamedParameter($entity->getEventId(), IQueryBuilder::PARAM_INT))
            ->set('room_token', $qb->createNamedParameter($entity->getRoomToken()))
            ->set('room_url', $qb->createNamedParameter($entity->getRoomUrl()))
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($entity->getId(), IQueryBuilder::PARAM_INT)));
        $qb->executeStatement();
        return $entity;
    }
}
