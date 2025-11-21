<?php namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        helper('jwt');
    }

    public function register()
{
    try {
        $request = service('request');
        $data = $request->getJSON(true);

        // Validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Username, email, dan password harus diisi'
            ], 400);
        }

        // Check if user exists
        $existingUser = $this->userModel->where('email', $data['email'])
                                      ->orWhere('username', $data['username'])
                                      ->first();
        
        if ($existingUser) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Username atau email sudah terdaftar'
            ], 400);
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Create user dengan data dari input
        $userData = [
            'username' => $data['username'], // PAKAI USERNAME DARI INPUT
            'email' => $data['email'],
            'password' => $hashedPassword
        ];

        $userId = $this->userModel->insert($userData);
        
        if (!$userId) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Gagal membuat user'
            ], 500);
        }

        // Get user data yang baru dibuat
        $newUser = $this->userModel->find($userId);

        // Generate JWT dengan data user baru
        $tokenData = [
            'user_id' => $newUser['id'],
            'username' => $newUser['username'], // PAKAI USERNAME BARU
            'email' => $newUser['email']
        ];

        helper('jwt');
        $token = generate_jwt($tokenData);

        return $this->responseJSON([
            'status' => true,
            'message' => 'Registrasi berhasil',
            'token' => $token,
            'user' => [
                'id' => $newUser['id'],
                'username' => $newUser['username'], // PAKAI DATA ASLI
                'email' => $newUser['email']
            ]
        ], 201);

    } catch (\Exception $e) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

   public function login()
{
    try {
        $request = service('request');
        $data = $request->getJSON(true);

        // Validation
        if (empty($data['email']) || empty($data['password'])) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Email dan password harus diisi'
            ], 400);
        }

        // Cari user di database
        $user = $this->userModel->where('email', $data['email'])->first();
        
        if (!$user) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 400);
        }

        // Verify password
        if (!password_verify($data['password'], $user['password'])) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 400);
        }

        // GENERATE JWT DENGAN DATA USER ASLI
        $userData = [
            'user_id' => $user['id'],
            'username' => $user['username'], // PAKAI USERNAME DARI DATABASE
            'email' => $user['email']
        ];

        helper('jwt');
        $token = generate_jwt($userData);

        return $this->responseJSON([
            'status' => true,
            'message' => 'Login berhasil!',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'], // PAKAI DATA ASLI
                'email' => $user['email']
            ]
        ]);

    } catch (\Exception $e) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Login Error: ' . $e->getMessage()
        ], 500);
    }
}

    public function me()
    {
        return $this->responseJSON([
            'status' => true,
            'user' => [
                'id' => 1,
                'username' => 'testuser',
                'email' => 'test@example.com'
            ]
        ]);
    }
}