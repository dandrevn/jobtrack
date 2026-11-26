<?php

namespace App\Models;

class UserModel extends \CodeIgniter\Shield\Models\UserModel
{
    protected function initialize(): void 
    {
        // Initialize Shield's built-in user model configuration.
        parent::initialize();

        // Allow JobTrack to save its additional name field.
        $this->allowedFields = array_merge(
            $this->allowedFields,
            ['name']
        );
    }
}
