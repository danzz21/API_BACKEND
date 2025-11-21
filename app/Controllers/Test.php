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
}