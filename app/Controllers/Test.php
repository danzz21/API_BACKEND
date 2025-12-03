<?php namespace App\Controllers;

class Test extends BaseController
{
    public function index()
    {
        try {
            $db = db_connect();
            $db->query('SELECT 1');
            
            return $this->responseJSON([
                'status' => true,
                'message' => 'Database connected successfully'
            ]);
        } catch (\Exception $e) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function users()
{
    try {
        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();
        
        // Hapus password
        $usersData = array_map(function($user) {
            unset($user['password']);
            return $user;
        }, $users);

        return $this->responseJSON([
            'status' => true,
            'data' => $usersData
        ]);

    } catch (\Exception $e) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}