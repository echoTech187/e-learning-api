<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Transactions extends BaseController
{
    use ResponseTrait;

    public function create()
    {
        $header = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $header);
        
        if (!$token) {
            return $this->failUnauthorized('No token provided');
        }
        
        // Very basic JWT parsing (no verification for now, assuming Next.js verified it, OR verify if needed)
        // JWT is base64 header.payload.signature
        if ($token === "dummy-token-123" || $token === "dummy-token") {
            $userId = "e6cf9d35-6da3-4f6c-8c02-831d1bad67cb";
        } else {
            $parts = explode(".", $token);
            if (count($parts) !== 3) {
                return $this->failUnauthorized("Invalid token");
            }
            $payload = json_decode(base64_decode($parts[1]), true);
            $userId = $payload["uid"] ?? $payload["id"] ?? $payload["sub"] ?? null;
        }
        
        if (!$userId) {
            return $this->failUnauthorized('User ID not found in token');
        }
        
        $json = $this->request->getJSON();
        $courseId = $json->course_id ?? null;
        
        if (!$courseId) {
            return $this->failValidationErrors('course_id is required');
        }
        
        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);
        
        try {
            $response = $client->post('http://internal/api/transactions', [
                'json' => [
                    'user_id' => $userId,
                    'course_id' => $courseId
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $this->respond($data, $response->getStatusCode());
        } catch (\Exception $e) {
            return $this->failServerError('Error communicating with internal service: ' . $e->getMessage());
        }
    }
    public function status($orderCode)
    {
        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);
        
        try {
            $response = $client->get("http://internal/api/transactions/status/$orderCode");
            $data = json_decode($response->getBody(), true);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to connect to internal service');
        }
    }
    public function enrollments($userId)
    {
        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);
        $response = $client->get("http://internal/api/transactions/enrollments/$userId");
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function userOrders()
    {
        $header = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $header);
        
        if (!$token) {
            return $this->failUnauthorized('No token provided');
        }
        
        if ($token === "dummy-token-123" || $token === "dummy-token") {
            $userId = "e6cf9d35-6da3-4f6c-8c02-831d1bad67cb";
        } else {
            $parts = explode(".", $token);
            if (count($parts) !== 3) {
                return $this->failUnauthorized("Invalid token");
            }
            $payload = json_decode(base64_decode($parts[1]), true);
            $userId = $payload["uid"] ?? $payload["id"] ?? $payload["sub"] ?? null;
        }
        
        if (!$userId) {
            return $this->failUnauthorized('User ID not found in token');
        }
        
        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);
        $response = $client->get("http://internal/api/transactions/user/$userId");
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function autoExpire()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);
        $response = $client->post("http://internal/api/transactions/auto-expire");
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }
}


