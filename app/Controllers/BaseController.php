<?php namespace App\Controllers;

use CodeIgniter\Controller;

class BaseController extends Controller
{
    /**
     * Constructor.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
    }

    protected function validateToken()
    {
        $request = service('request');
        $token = $request->getHeaderLine('Authorization');
        
        if (!$token) {
            return null;
        }

        $token = str_replace('Bearer ', '', $token);

        helper('jwt');
        return validate_jwt($token);
    }

    protected function responseJSON($data, $status = 200)
    {
        return $this->response
            ->setStatusCode($status)
            ->setContentType('application/json')
            ->setJSON($data);
    }

    protected function getCurrentUserId()
    {
        $tokenData = $this->validateToken();
        return $tokenData ? $tokenData['user_id'] : null;
    }
}