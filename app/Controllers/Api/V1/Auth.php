<?php
namespace App\Controllers\Api\V1;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

class Auth extends ResourceController
{
    protected $format = 'json';
    private $jwtSecret = 'MY_SUPER_SECRET_KEY_123!_VERY_LONG_KEY_456789';

    private function generateJWT($user) {
        $payload = [
            'iat' => time(),
            'exp' => time() + 86400,
            'uid' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'role' => $user['role'],
            'photo' => $user['photo'] ?? null
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function login()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? '';
        $password = $json['password'] ?? '';

        if (!$email || !$password) return $this->failValidationErrors('Mohon lengkapi email dan password Anda terlebih dahulu.');

        $client = \Config\Services::curlrequest();
        try {
            $response = $client->get("http://internal/api/users/find_by_email?email=" . urlencode($email));
            $user = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return $this->failUnauthorized('Alamat email atau password yang Anda masukkan tidak sesuai. Mohon periksa kembali.');
        }

        if (!password_verify($password, $user['password'])) return $this->failUnauthorized('Alamat email atau password yang Anda masukkan tidak sesuai. Mohon periksa kembali.');
        if (!$user['is_active']) return $this->failUnauthorized('Mohon maaf, akun Anda saat ini sedang tidak aktif.');

        return $this->respond([
            'message' => 'Selamat datang! Anda berhasil masuk.',
            'token' => $this->generateJWT($user),
            'user' => [
                'id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']
            ]
        ]);
    }

    public function register()
    {
        $json = $this->request->getJSON(true);
        if (empty($json['name']) || empty($json['email']) || empty($json['password'])) {
            return $this->failValidationErrors('Mohon isi seluruh data yang diperlukan untuk pendaftaran.');
        }

        $data = [
            'name' => $json['name'],
            'email' => strtolower($json['email']),
            'password' => password_hash($json['password'], PASSWORD_DEFAULT),
            'role' => 'pending', 
            'is_active' => 1
        ];

        $client = \Config\Services::curlrequest();
        try {
            try {
                $client->get("http://internal/api/users/find_by_email?email=" . urlencode($data['email']));
                return $this->failValidationErrors(['email' => 'Email tersebut sudah terdaftar. Silakan gunakan alamat email lain atau masuk ke akun Anda.']);
            } catch (\Exception $e) {}

            $response = $client->post('http://internal/api/users', ['json' => $data]);
            $created = json_decode($response->getBody(), true);
            
            $userResponse = $client->get("http://internal/api/users/find_by_email?email=" . urlencode($data['email']));
            $user = json_decode($userResponse->getBody(), true);

            return $this->respondCreated([
                'message' => 'Pendaftaran berhasil! Akun Anda telah siap.',
                'token' => $this->generateJWT($user),
                'user' => [
                    'id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Mohon maaf, terjadi kendala sistem saat memproses pendaftaran Anda. Silakan coba kembali beberapa saat lagi.');
        }
    }

    public function onboarding()
    {
        $json = $this->request->getJSON(true);
        if (empty($json['user_id']) || empty($json['role'])) {
            return $this->failValidationErrors('Mohon maaf, data tidak lengkap. Silakan coba lagi.');
        }

        $profile = [];
        if (!empty($json['phone'])) $profile['phone'] = $json['phone']; 
        
        $client = \Config\Services::curlrequest();
        try {
            $response = $client->post('http://internal/api/users/onboarding', [
                'json' => [
                    'user_id' => $json['user_id'],
                    'role' => $json['role'],
                    'profile' => $profile
                ]
            ]);
            $updatedUser = json_decode($response->getBody(), true);

            return $this->respond([
                'message' => 'Profil berhasil disimpan! Selamat datang di EduNusa.',
                'token' => $this->generateJWT($updatedUser),
                'user' => [
                    'id' => $updatedUser['id'], 'name' => $updatedUser['name'], 'email' => $updatedUser['email'], 'role' => $updatedUser['role']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->failServerError('Mohon maaf, kami mengalami kendala saat menyimpan profil Anda. Silakan coba kembali.');
        }
    }
}
