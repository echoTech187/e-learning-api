<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class Categories extends ResourceController
{
    public function index()
    {
        try {
            $client = \Config\Services::curlrequest(['http_errors' => false]);
            $response = $client->get('http://internal/api/categories');
            return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
    }

    public function show($id = null)
    {
        try {
            $client = \Config\Services::curlrequest(['http_errors' => false]);
            $response = $client->get('http://internal/api/categories/' . $id);
            return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
}
