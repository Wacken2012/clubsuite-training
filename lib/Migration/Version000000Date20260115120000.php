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

class Version000000Date20260115120000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('training_attendance')) {
            $table = $schema->getTable('training_attendance');
            
            // Add member_id column if not present
            if (!$table->hasColumn('member_id')) {
                $table->addColumn('member_id', 'integer', ['notnull' => false, 'default' => null]);
            }
            
            // Add checked_in_at timestamp
            if (!$table->hasColumn('checked_in_at')) {
                $table->addColumn('checked_in_at', 'datetime', ['notnull' => false, 'default' => null]);
            }
            
            // Add checked_out_at timestamp
            if (!$table->hasColumn('checked_out_at')) {
                $table->addColumn('checked_out_at', 'datetime', ['notnull' => false, 'default' => null]);
            }
        }
        
        return $schema;
    }
}
