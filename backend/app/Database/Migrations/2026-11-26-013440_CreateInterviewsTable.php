<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInterviewsTable extends Migration
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
            'interview_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'scheduled_at' => [
                'type' => 'DATETIME',
            ],
            'location_or_link' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'scheduled',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('scheduled_at');

        $this->forge->addForeignKey(
            'application_id',
            'job_applications',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('interviews');
    }

    public function down()
    {
        $this->forge->dropTable('interviews');
    }
}
