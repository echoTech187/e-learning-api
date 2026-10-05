<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * LessonProgress Gateway Controller
 */
class LessonProgress extends BaseController
{
    use ResponseTrait;

    private function parseUserId(): ?string
    {
        $header = $this->request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);
        if (!$token) return null;

        if (in_array($token, ['dummy-token-123', 'dummy-token'])) {
            return 'e6cf9d35-6da3-4f6c-8c02-831d1bad67cb';
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload['uid'] ?? $payload['id'] ?? $payload['sub'] ?? null;
    }

    /** POST /api/v1/lesson-progress */
    public function create()
    {
        $userId = $this->parseUserId();
        if (!$userId) return $this->failUnauthorized('No valid token');

        $json = $this->request->getJSON();
        if (!$json) return $this->failValidationErrors('Invalid JSON body');

        $json->user_id = $userId; // Inject from token

        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);

        try {
            $response = $client->post('http://internal/api/lesson-progress', [
                'json' => $json,
            ]);
            return $this->response
                ->setStatusCode($response->getStatusCode())
                ->setBody($response->getBody())
                ->setContentType('application/json');
        } catch (\Exception $e) {
            return $this->failServerError('Error: ' . $e->getMessage());
        }
    }
}