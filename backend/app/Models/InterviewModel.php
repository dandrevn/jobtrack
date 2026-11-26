<?php

namespace App\Models;

use CodeIgniter\Model;

class InterviewModel extends Model
{
    protected $table            = 'interviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'application_id',
        'interview_type',
        'scheduled_at',
        'location_or_link',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'application_id' => 'required|integer',
        'interview_type' => 'required|max_length[50]',
        'scheduled_at' => 'required|valid_date[Y-m-d H:i:s]',
        'location_or_link' => 'permit_empty',
        'notes' => 'permit_empty',
        'status' => 'required|in_list[scheduled,completed,cancelled,rescheduled]',
    ];
}