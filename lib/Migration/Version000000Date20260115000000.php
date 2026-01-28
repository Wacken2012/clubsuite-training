<?php

declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Creates talk_rooms table for Nextcloud Talk integration.
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
class Version000000Date20260115000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('clubsuite_training_talk_rooms')) {
            $table = $schema->createTable('clubsuite_training_talk_rooms');
            $table->addColumn('id', 'integer', [
                'autoincrement' => true,
                'notnull' => true,
            ]);
            $table->addColumn('event_id', 'integer', [
                'notnull' => true,
            ]);
            $table->addColumn('room_token', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('room_url', 'string', [
                'notnull' => false,
                'length' => 500,
            ]);
            $table->addColumn('created_at', 'datetime', [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['event_id'], 'talk_rooms_event_id_idx');
            $table->addUniqueIndex(['room_token'], 'talk_rooms_token_idx');
        }

        return $schema;
    }
}
