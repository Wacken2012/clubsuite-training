<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000000Date20260105000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('training_group')) {
            $table = $schema->createTable('training_group');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('description', 'text', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('training_event')) {
            $table = $schema->createTable('training_event');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('group_id', 'integer', ['notnull' => true]);
            $table->addColumn('date', 'date', ['notnull' => true]);
            $table->addColumn('start_time', 'string', ['length' => 20, 'notnull' => true]);
            $table->addColumn('end_time', 'string', ['length' => 20, 'notnull' => true]);
            $table->addColumn('location', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('notes', 'text', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('training_attendance')) {
            $table = $schema->createTable('training_attendance');
            $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
            $table->addColumn('event_id', 'integer', ['notnull' => true]);
            $table->addColumn('user_id', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('status', 'string', ['length' => 20, 'default' => 'absent', 'notnull' => true]);
            $table->setPrimaryKey(['id']);
        }
        
        return $schema;
    }
}
