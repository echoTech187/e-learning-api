<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class UiData extends ResourceController
{
    public function testimonials()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/testimonials');
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function platformSettings()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/platform-settings');
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function coupons()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/coupons');
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function stats()
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/stats');
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }

    public function courseDetail($courseId = null)
    {
        $client = \Config\Services::curlrequest(['http_errors' => false]);
        $response = $client->get('http://internal/api/course-detail/' . $courseId);
        return $this->response->setStatusCode($response->getStatusCode())->setBody($response->getBody());
    }
}
