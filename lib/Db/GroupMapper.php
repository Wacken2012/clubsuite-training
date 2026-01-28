<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\IDBConnection;

class GroupMapper {
    private IDBConnection $db;
    private string $table = 'training_group';

    public function __construct(IDBConnection $db) {
        $this->db = $db;
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table);
        $rows = $qb->executeQuery()->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $g = GroupEntity::build([
                'name' => $r['name'],
                'description' => $r['description'] ?? null,
            ]);
            $g->setId((int)$r['id']);
            $out[] = $g;
        }
        return $out;
    }

    public function findById(int $id): ?GroupEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->table)->where($qb->expr()->eq('id', $qb->createNamedParameter($id)));
        $r = $qb->executeQuery()->fetch();
        if (!$r) {
            return null;
        }
        $g = GroupEntity::build([
            'name' => $r['name'],
            'description' => $r['description'] ?? null,
        ]);
        $g->setId((int)$r['id']);
        return $g;
    }

    public function create(GroupEntity $g): GroupEntity {
        $qb = $this->db->getQueryBuilder();
        $qb->insert($this->table)
           ->values([
               'name' => $qb->createNamedParameter($g->getName()),
               'description' => $qb->createNamedParameter($g->getDescription()),
           ]);
        $qb->executeStatement();
        $g->setId((int)$this->db->lastInsertId($this->table));
        return $g;
    }
}
