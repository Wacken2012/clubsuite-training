<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\IDBConnection;

class EventMapper {
    private IDBConnection $db;
    private string $table = 'training_event';

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->orderBy('date', 'DESC');
        $rows = $qb->executeQuery()->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $e = EventEntity::build([
                'groupId' => (int)$r['group_id'],
                'date' => $r['date'],
                'startTime' => $r['start_time'],
                'endTime' => $r['end_time'],
                'location' => $r['location'] ?? null,
                'notes' => $r['notes'] ?? null,
                'title' => $r['title'] ?? null,
            ]);
            $e->setId((int)$r['id']);
            $out[] = $e;
        }
        return $out;
    }

    public function findById(int $id): ?EventEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        $r = $qb->executeQuery()->fetch();
        if (!$r) {
            return null;
        }
        $e = EventEntity::build([
            'groupId' => (int)$r['group_id'],
            'date' => $r['date'],
            'startTime' => $r['start_time'],
            'endTime' => $r['end_time'],
            'location' => $r['location'] ?? null,
            'notes' => $r['notes'] ?? null,
            'title' => $r['title'] ?? null,
        ]);
        $e->setId((int)$r['id']);
        return $e;
    }

    public function create(EventEntity $e): EventEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->insert($this->table)
           ->values([
               'group_id' => $qb->createNamedParameter($e->getGroupId()),
               'date' => $qb->createNamedParameter($e->getDate()),
               'start_time' => $qb->createNamedParameter($e->getStartTime()),
               'end_time' => $qb->createNamedParameter($e->getEndTime()),
               'location' => $qb->createNamedParameter($e->getLocation()),
               'notes' => $qb->createNamedParameter($e->getNotes()),
               'title' => $qb->createNamedParameter($e->getTitle()),
           ]);
        $qb->executeStatement();
        $e->setId((int)$this->db->lastInsertId($this->table));
        return $e;
    }
}
