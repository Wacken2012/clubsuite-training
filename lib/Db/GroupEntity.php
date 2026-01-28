<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Db;

use OCP\AppFramework\Db\Entity;
use JsonSerializable;

class GroupEntity extends Entity implements JsonSerializable {
    protected string $name;
    protected ?string $description = null;

    public function __construct() {
        // id ist von Entity geerbt
    }

    public static function build(array $params): static {
        $e = new static();
        if (isset($params['name'])) {
            $e->setName($params['name']);
        }
        if (isset($params['description'])) {
            $e->setDescription($params['description']);
        }
        return $e;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description
        ];
    }

    public function getName(): string { return $this->name; }
    public function setName(string $n): void { $this->name = $n; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $d): void { $this->description = $d; }
}
