<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Webhooks extends BaseController
{
    use ResponseTrait;

    public function midtrans()
    {
        $client = \Config\Services::curlrequest();
        $json = $this->request->getJSON(true);
        
        try {
            $response = $client->post('http://internal/api/webhooks/midtrans', [
                'json' => $json
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->failServerError('Error forwarding webhook: ' . $e->getMessage());
        }
    }
}