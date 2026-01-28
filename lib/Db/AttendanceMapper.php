<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\IDBConnection;

class AttendanceMapper {
    private IDBConnection $db;
    private string $table = 'oc_training_attendance';

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function findByEvent(int $eventId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->where($qb->expr()->eq('event_id', $qb->createNamedParameter($eventId)))->orderBy('checked_in_at', 'DESC');
        $rows = $qb->executeQuery()->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $a = AttendanceEntity::build([
                'eventId' => (int)$r['event_id'],
                'userId' => $r['user_id'],
                'status' => $r['status'] ?? 'absent',
                'memberId' => $r['member_id'] ? (int)$r['member_id'] : null,
                'checkedInAt' => $r['checked_in_at'] ? new \DateTime($r['checked_in_at']) : null,
                'checkedOutAt' => $r['checked_out_at'] ? new \DateTime($r['checked_out_at']) : null,
            ]);
            $a->setId((int)$r['id']);
            $out[] = $a;
        }
        return $out;
    }

    public function create(AttendanceEntity $a): AttendanceEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->insert($this->table)
           ->values([
               'event_id' => $qb->createNamedParameter($a->getEventId()),
               'user_id' => $qb->createNamedParameter($a->getUserId()),
               'status' => $qb->createNamedParameter($a->getStatus()),
               'member_id' => $qb->createNamedParameter($a->getMemberId()),
               'checked_in_at' => $qb->createNamedParameter($a->getCheckedInAt()?->format('Y-m-d H:i:s')),
               'checked_out_at' => $qb->createNamedParameter($a->getCheckedOutAt()?->format('Y-m-d H:i:s')),
           ]);
        $qb->executeStatement();
        $a->setId((int)$this->db->lastInsertId($this->table));
        return $a;
    }

    public function updateStatus(int $id, string $status): void {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->table)
           ->set('status', $qb->createNamedParameter($status))
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        $qb->executeStatement();
    }

    public function findWithEventByUser(string $userId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('a.*', 'e.date', 'e.start_time', 'e.location', 'e.end_time', 'e.group_id')
           ->from($this->table, 'a')
           ->join('a', 'training_event', 'e', 'a.event_id = e.id')
           ->where($qb->expr()->eq('a.user_id', $qb->createNamedParameter($userId)))
           ->orderBy('e.date', 'DESC');
        return $qb->executeQuery()->fetchAll();
    }

    public function findByMember(int $memberId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->where($qb->expr()->eq('member_id', $qb->createNamedParameter($memberId)))->orderBy('checked_in_at', 'DESC');
        $rows = $qb->executeQuery()->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $a = AttendanceEntity::build([
                'eventId' => (int)$r['event_id'],
                'userId' => $r['user_id'],
                'status' => $r['status'] ?? 'absent',
                'memberId' => $r['member_id'] ? (int)$r['member_id'] : null,
                'checkedInAt' => $r['checked_in_at'] ? new \DateTime($r['checked_in_at']) : null,
                'checkedOutAt' => $r['checked_out_at'] ? new \DateTime($r['checked_out_at']) : null,
            ]);
            $a->setId((int)$r['id']);
            $out[] = $a;
        }
        return $out;
    }

    public function findById(int $id): ?AttendanceEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        $r = $qb->executeQuery()->fetch();
        if (!$r) {
            return null;
        }
        $a = AttendanceEntity::build([
            'eventId' => (int)$r['event_id'],
            'userId' => $r['user_id'],
            'status' => $r['status'] ?? 'absent',
            'memberId' => $r['member_id'] ? (int)$r['member_id'] : null,
            'checkedInAt' => $r['checked_in_at'] ? new \DateTime($r['checked_in_at']) : null,
            'checkedOutAt' => $r['checked_out_at'] ? new \DateTime($r['checked_out_at']) : null,
        ]);
        $a->setId((int)$r['id']);
        return $a;
    }

    public function update(AttendanceEntity $a): AttendanceEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->table)
           ->set('event_id', $qb->createNamedParameter($a->getEventId()))
           ->set('user_id', $qb->createNamedParameter($a->getUserId()))
           ->set('status', $qb->createNamedParameter($a->getStatus()))
           ->set('member_id', $qb->createNamedParameter($a->getMemberId()))
           ->set('checked_in_at', $qb->createNamedParameter($a->getCheckedInAt()?->format('Y-m-d H:i:s')))
           ->set('checked_out_at', $qb->createNamedParameter($a->getCheckedOutAt()?->format('Y-m-d H:i:s')))
           ->where($qb->expr()->eq('id', $qb->createNamedParameter($a->getId())));
        $qb->executeStatement();
        return $a;
    }

}
