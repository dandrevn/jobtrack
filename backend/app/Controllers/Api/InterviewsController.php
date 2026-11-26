<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\InterviewModel;
use App\Models\JobApplicationModel;

class InterviewsController extends BaseController
{
    
    public function index($applicationId)
    {
        // Retrieve the authenticated user.
        $user = auth()->user();

        // Verify that the application belongs to this user.
        $applicationModel = new JobApplicationModel();

        $application = $applicationModel
            ->where('user_id', $user->id)
            ->find($applicationId);

        if ($application === null) {
            // Return 404 if the application does not belong to the user.
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Job application not found.',
                ]);
        }

        // Retrieve interviews associated with the verified application.
        $interviewModel = new InterviewModel();

        $interviews = $interviewModel
            ->where('application_id', $applicationId)
            ->orderBy('scheduled_at', 'ASC')
            ->findAll();

        // Return the list of interviews.
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $interviews,
        ]);
    }


    public function create($applicationId)
    {
        // Retrieve the authenticated user.
        $user = auth()->user();

        // Initialize the job application model.
        $applicationModel = new JobApplicationModel();

        // Verify that the application belongs to this user.
        $application = $applicationModel
            ->where('user_id', $user->id)
            ->find($applicationId);

        if ($application === null) {
            // Prevent users from scheduling interviews for other users' applications.
            return $this->response
                ->setStatusCode(404)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Job application not found.',
                ]);
        }

        // Read the interview details from the JSON request.
        $data = $this->request->getJSON(true) ?? [];

        // Assign the verified parent application ID.
        $data['application_id'] = $applicationId;

        // Initialize the interview model.
        $interviewModel = new InterviewModel();

        // Save the new interview and apply model validation.
        if (! $interviewModel->insert($data)) {
            // Return validation errors if insertion fails.
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $interviewModel->errors(),
                ]);
        }

        // Retrieve the newly created interview.
        $interview = $interviewModel->find(
            $interviewModel->getInsertID()
        );

        // Return the created interview.
        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status'    => 'success',
                'message'   => 'Interview scheduled successfully.',
                'interview' => $interview,
            ]);
    }

}
