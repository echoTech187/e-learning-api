<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class Courses extends ResourceController
{
    public function index()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/courses');
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function show($slug = null)
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/courses/' . $slug);
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }
}
