<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Service;

use OCA\ClubSuiteTraining\Db\GroupMapper;
use OCA\ClubSuiteTraining\Db\GroupEntity;

class GroupService {
    private GroupMapper $mapper;

    public function __construct(GroupMapper $mapper) {
        $this->mapper = $mapper;
    }

    public function listGroups(): array {
        return $this->mapper->findAll();
    }

    public function getGroup(int $id): ?GroupEntity {
        return $this->mapper->findById($id);
    }

    public function createGroup(string $name, ?string $description = null): GroupEntity {
        $g = GroupEntity::build([
            'name' => $name,
            'description' => $description,
        ]);
        return $this->mapper->create($g);
    }
}
