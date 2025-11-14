<?php

namespace App\Controllers;

use App\Models\UserRepository;
use App\Helpers\Validator;
use App\Helpers\Logger;
use App\Helpers\CSRF;
use App\Helpers\Session;
use App\Middleware\Auth;

/**
 * Authentication controller for user login/register/logout
 */
class AuthController
{
    private $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    /**
     * Register new user
     *
     * @param array $data User data (username, email, password)
     * @return int User ID
     * @throws \Exception
     */
    public function register(array $data)
    {
        try {
            // Validate CSRF token
            CSRF::verify();

            // Validate input
            $validator = new Validator($data);
            $validator->rule('username', 'required|min:3|max:50|alphanumeric', 'Username')
                      ->rule('email', 'required|email|max:100', 'Email')
                      ->rule('password', 'required|min:6', 'Password');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];
                foreach ($errors as $field => $messages) {
                    $errorMessages[] = implode(', ', $messages);
                }
                throw new \Exception(implode('; ', $errorMessages));
            }

            // Check if username already exists
            if ($this->userRepo->findByUsername($data['username'])) {
                throw new \Exception('Username already taken');
            }

            // Check if email already exists
            if ($this->userRepo->findByEmail($data['email'])) {
                throw new \Exception('Email already registered');
            }

            // Create user
            $userData = [
                'username' => Validator::sanitize($data['username']),
                'email' => Validator::sanitize($data['email']),
                'password' => $data['password'] // Will be hashed in repository
            ];

            $userId = $this->userRepo->createUser($userData);
            Logger::info('New user registered', ['username' => $userData['username']]);

            Session::flash('success', 'Registration successful! Please log in.');
            return $userId;
        } catch (\Exception $e) {
            Logger::error('Error registering user: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log in user
     *
     * @param array $data Login credentials (username, password)
     * @return array User data
     * @throws \Exception
     */
    public function login(array $data)
    {
        try {
            // Validate CSRF token
            CSRF::verify();

            // Validate input
            $validator = new Validator($data);
            $validator->rule('username', 'required', 'Username')
                      ->rule('password', 'required', 'Password');

            if ($validator->fails()) {
                throw new \Exception('Username and password are required');
            }

            // Verify credentials
            $user = $this->userRepo->verifyCredentials($data['username'], $data['password']);

            if (!$user) {
                throw new \Exception('Invalid username or password');
            }

            // Log in user
            Auth::login($user);

            Session::flash('success', 'Welcome back, ' . $user['username'] . '!');
            return $user;
        } catch (\Exception $e) {
            Logger::error('Login error: ' . $e->getMessage());
            Session::flash('error', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log out user
     *
     * @return void
     */
    public function logout()
    {
        $username = Auth::username();
        Auth::logout();
        Logger::info('User logged out', ['username' => $username]);
        Session::flash('success', 'Logged out successfully');
    }
}
