<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationStatusHistoryModel extends Model
{
    protected $table            = 'application_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'application_id',
        'old_status',
        'new_status',
        'changed_at',
        'notes',
    ];

    protected $validationRules = [
        'application_id' => 'required|integer',
        'old_status' => 'permit_empty|in_list[interested,applied,screening,interview,assessment,offered,hired,rejected,withdrawn]',
        'new_status' => 'required|in_list[interested,applied,screening,interview,assessment,offered,hired,rejected,withdrawn]',
        'notes' => 'permit_empty',
    ];
}