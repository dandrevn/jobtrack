<?php

namespace App\Controllers\Api;
use App\Models\ApplicationStatusHistoryModel;
use CodeIgniter\I18n\Time;
use Config\Database;
use App\Models\JobApplicationModel;
use App\Controllers\BaseController;

class JobApplicationsController extends BaseController
{
    public function index()
        {
            // Retrieve the authenticated user from Shield.
            $user = auth()->user();

            // Initialize the job application model.
            $applicationModel = new JobApplicationModel();

            // Retrieve only applications owned by this user.
            $applications = $applicationModel
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            // Return the user's applications as JSON.
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $applications,
            ]);
        }


    
    public function create()
        {
            // Read the JSON payload from the frontend.
            $data = $this->request->getJSON(true) ?? [];

            // Retrieve the authenticated user.
            $user = auth()->user();

            // Initialize the required models and database connection.
            $applicationModel = new JobApplicationModel();
            $historyModel = new ApplicationStatusHistoryModel();
            $db = Database::connect();

            // Always assign ownership using the authenticated user's ID.
            $data['user_id'] = $user->id;

            // Start a transaction so both inserts succeed or fail together.
            $db->transBegin();

            // Insert the job application.
            if (! $applicationModel->insert($data)) {
                $db->transRollback();

                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'errors' => $applicationModel->errors(),
                    ]);
            }

            // Retrieve the newly created application's ID and saved data.
            $applicationId = $applicationModel->getInsertID();
            $application = $applicationModel->find($applicationId);

            // Record the application's initial status.
            $historyInserted = $historyModel->insert([
                'application_id' => $applicationId,
                'old_status'     => null,
                'new_status'     => $application['status'],
                'changed_at'     => Time::now()->toDateTimeString(),
                'notes'          => 'Application created.',
            ]);

            // Roll back if the history record could not be saved.
            if (! $historyInserted || ! $db->transStatus()) {
                $db->transRollback();

                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Unable to create the application status history.',
                    ]);
            }

            // Commit both database operations.
            $db->transCommit();

            // Return the newly created application.
            return $this->response
                ->setStatusCode(201)
                ->setJSON([
                    'status'      => 'success',
                    'message'     => 'Job application created successfully.',
                    'application' => $application,
                ]);
        }

    public function show($id)
        {
            // Retrieve the authenticated user.
            $user = auth()->user();

            // Initialize the application model.
            $applicationModel = new JobApplicationModel();

            // Find the application only if it belongs to this user.
            $application = $applicationModel
                ->where('user_id', $user->id)
                ->find($id);

            if ($application === null) {
                // Do not reveal whether another user's application exists.
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Job application not found.',
                    ]);
            }

            // Return the requested application.
            return $this->response->setJSON([
                'status'      => 'success',
                'application' => $application,
            ]);
        }


    
    public function update($id)
        {
            // Retrieve the authenticated user.
            $user = auth()->user();

            // Initialize the models and database connection.
            $applicationModel = new JobApplicationModel();
            $historyModel = new ApplicationStatusHistoryModel();
            $db = Database::connect();

            // Find the application only if it belongs to this user.
            $application = $applicationModel
                ->where('user_id', $user->id)
                ->find($id);

            if ($application === null) {
                // Do not reveal whether another user's application exists.
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Job application not found.',
                    ]);
            }

            // Read the updated application details.
            $data = $this->request->getJSON(true) ?? [];

            // Prevent clients from changing protected fields.
            unset($data['id'], $data['user_id'], $data['created_at']);

            if ($data === []) {
                // Reject requests that contain no editable fields.
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'No application details were provided.',
                    ]);
            }

            // Compare the existing status with the requested status.
            $oldStatus = $application['status'];
            $newStatus = $data['status'] ?? $oldStatus;
            $statusChanged = $newStatus !== $oldStatus;

            // Start a transaction for the application and history updates.
            $db->transBegin();

            // Update the application and apply model validation.
            if (! $applicationModel->update($id, $data)) {
                $db->transRollback();

                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'status' => 'error',
                        'errors' => $applicationModel->errors(),
                    ]);
            }

            // Record the transition only when the status actually changes.
            if ($statusChanged) {
                $historyInserted = $historyModel->insert([
                    'application_id' => $id,
                    'old_status'     => $oldStatus,
                    'new_status'     => $newStatus,
                    'changed_at'     => Time::now()->toDateTimeString(),
                    'notes'          => 'Application status updated.',
                ]);

                // Roll back if the history record could not be saved.
                if (! $historyInserted || ! $db->transStatus()) {
                    $db->transRollback();

                    return $this->response
                        ->setStatusCode(500)
                        ->setJSON([
                            'status'  => 'error',
                            'message' => 'Unable to record the application status change.',
                        ]);
                }
            }

            // Roll back if any database operation failed.
            if (! $db->transStatus()) {
                $db->transRollback();

                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Unable to update the job application.',
                    ]);
            }

            // Commit the application update and history entry.
            $db->transCommit();

            // Retrieve the updated application, ensuring ownership.
            $updatedApplication = $applicationModel
                ->where('user_id', $user->id)
                ->find($id);

            // Return the updated application.
            return $this->response->setJSON([
                'status'      => 'success',
                'message'     => 'Job application updated successfully.',
                'application' => $updatedApplication,
            ]);
        }

    
    public function delete($id)
        {
            // Retrieve the authenticated user.
            $user = auth()->user();

            // Initialize the application model.
            $applicationModel = new JobApplicationModel();

            // Find the application only if it belongs to this user.
            $application = $applicationModel
                ->where('user_id', $user->id)
                ->find($id);

            if ($application === null) {
                // Return 404 if the application does not belong to this user.
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Job application not found.',
                    ]);
            }

            // Delete the verified application.
            if (! $applicationModel->delete($id)) {
                // Return an error if deletion fails.
                return $this->response
                    ->setStatusCode(500)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Unable to delete the job application.',
                    ]);
            }

            // Confirm successful deletion.
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Job application deleted successfully.',
            ]);
        }
    
    public function history($id)
        {
            // Retrieve the authenticated user.
            $user = auth()->user();

            // Verify that the application belongs to this user.
            $applicationModel = new JobApplicationModel();

            $application = $applicationModel
                ->where('user_id', $user->id)
                ->find($id);

            if ($application === null) {
                // Do not reveal applications owned by other users.
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'status'  => 'error',
                        'message' => 'Job application not found.',
                    ]);
            }

            // Retrieve the application's status history.
            $historyModel = new ApplicationStatusHistoryModel();

            $history = $historyModel
                ->where('application_id', $id)
                ->orderBy('changed_at', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            // Return the history in chronological order.
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $history,
            ]);
        }




}