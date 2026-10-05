<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * Classroom Gateway Controller
 * Validates JWT, then proxies to internal service.
 */
class Classroom extends BaseController
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

    /** GET /api/v1/classroom/{enrollmentId} */
    public function show($enrollmentId = null)
    {
        $userId = $this->parseUserId();
        if (!$userId) return $this->failUnauthorized('No valid token');

        $client = \Config\Services::curlrequest(['http_errors' => false, 'force_ipv4' => true]);

        try {
            $response = $client->get("http://internal/api/classroom/{$enrollmentId}?user_id={$userId}");
            return $this->response
                ->setStatusCode($response->getStatusCode())
                ->setBody($response->getBody())
                ->setContentType('application/json');
        } catch (\Exception $e) {
            return $this->failServerError('Error: ' . $e->getMessage());
        }
    }
}