<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApplicationStatusHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'old_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'new_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'changed_at' => [
                'type' => 'DATETIME',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['application_id', 'changed_at']);

        $this->forge->addForeignKey(
            'application_id',
            'job_applications',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('application_status_history');
    }

    public function down()
    {
        $this->forge->dropTable('application_status_history');
    }
}
