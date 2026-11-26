<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function register()
    {
        // Read the JSON data sent by the React frontend.
        $data = $this->request->getJSON(true) ?? [];

        // Validate the registration fields.
        $rules = [
            'name'                  => 'required|min_length[2]|max_length[150]',
            'email'                 => 'required|valid_email|max_length[255]',
            'password'              => 'required|min_length[8]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (! $this->validateData($data, $rules)) {
            // Return validation errors if the submitted data is invalid.
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        // Get the configured Shield user provider.
        $users = auth()->getProvider();

        // Check whether the email is already registered.
        $existingUser = $users->findByCredentials([
            'email' => $data['email'],
        ]);

        if ($existingUser !== null) {
            return $this->response
                ->setStatusCode(409)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Email address is already registered.',
                ]);
        }

        // Create a Shield User entity with the submitted information.
        $user = $users->createNewUser([
            'name'     => $data['name'],
            'username' => null,
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        // Save the user and let Shield handle password hashing and identity creation.
        $users->save($user);

        // Retrieve the newly created user's database ID.
        $userId = $users->getInsertID();

        // Return a successful registration response.
        return $this->response
            ->setStatusCode(201)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Account successfully created.',
                'user'    => [
                    'id'    => $userId,
                    'name'  => $data['name'],
                    'email' => $data['email'],
                ],
            ]);
    }


public function login()
    {
        // Read the JSON credentials sent by the React frontend.
        $data = $this->request->getJSON(true) ?? [];

        // Validate the required login fields.
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validateData($data, $rules)) {
            // Return validation errors for missing or invalid fields.
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $this->validator->getErrors(),
                ]);
        }

        // Check credentials without creating a persistent login session.
        $result = auth()->check([
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        if (! $result->isOK()) {
            // Reject invalid credentials.
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Invalid email or password.',
                ]);
        }

        // Retrieve the authenticated Shield user.
        $user = $result->extraInfo();

        // Generate a personal access token for the React application.
        $token = $user->generateAccessToken('JobTrack React App');

        // Return the raw token only once so the frontend can store it.
        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Login successful.',
                'token'   => $token->raw_token,
                'user'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $data['email'],
                ],
            ]);
    }



public function logout()
    {
        // Retrieve the authenticated user and current access token.
        $user = auth()->user();
        $accessToken = $user->currentAccessToken();

        if ($accessToken === null) {
            // Stop if no current access token is available.
            return $this->response
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'No active access token found.',
                ]);
        }

        // Revoke only the token used for this request.
        $user->revokeAccessTokenBySecret($accessToken->secret);

        // Confirm successful logout.
        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Logout successful.',
        ]);
    }



}
