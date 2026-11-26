<?php

namespace App\Models;

use CodeIgniter\Model;

class JobApplicationModel extends Model
{
    protected $table            = 'job_applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'user_id',
        'company_name',
        'job_title',
        'job_type',
        'work_setup',
        'location',
        'salary_min',
        'salary_max',
        'salary_currency',
        'job_url',
        'source',
        'date_applied',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'company_name' => 'required|max_length[150]',
        'job_title' => 'required|max_length[150]',
        'job_type' => 'permit_empty|max_length[50]',
        'work_setup' => 'permit_empty|max_length[50]',
        'location' => 'permit_empty|max_length[150]',
        'salary_min' => 'permit_empty|decimal',
        'salary_max' => 'permit_empty|decimal',
        'salary_currency' => 'required|max_length[10]',
        'job_url' => 'permit_empty',
        'source' => 'permit_empty|max_length[100]',
        'date_applied' => 'permit_empty|valid_date[Y-m-d]',
        'status' => 'required|in_list[interested,applied,screening,interview,assessment,offered,hired,rejected,withdrawn]',
        'notes' => 'permit_empty',
    ];
}
