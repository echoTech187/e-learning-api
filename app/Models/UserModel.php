<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'id', 'name', 'email', 'password', 'role', 'photo',
        'phone', 'is_active', 'remember_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $validationRules = [
        'name'  => 'required|min_length[3]|max_length[150]',
        'email' => 'required|valid_email|max_length[150]',
        'role'  => 'required|in_list[admin,instructor,student,parent]',
    ];

    /**
     * Cari user berdasarkan email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Verifikasi password login
     */
    public function verifyPassword(string $plain, string $hashed): bool
    {
        return password_verify($plain, $hashed);
    }

    /**
     * Set session setelah login berhasil
     */
    public function setLoginSession(array $user): void
    {
        session()->set([
            'user_id'     => $user['id'],
            'user_name'   => $user['name'],
            'user_email'  => $user['email'],
            'user_role'   => $user['role'],
            'user_photo'  => $user['photo'],
            'is_logged_in'=> true,
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        return $this->where('role', $role)->where('deleted_at', null)->findAll();
    }

    /**
     * Get dashboard stats
     */
    public function getStats(): array
    {
        return [
            'total_students'    => $this->where('role', 'student')->countAllResults(),
            'total_instructors' => $this->where('role', 'instructor')->countAllResults(),
            'total_parents'     => $this->where('role', 'parent')->countAllResults(),
            'total_admins'      => $this->where('role', 'admin')->countAllResults(),
        ];
    }

    protected $beforeInsert = ['generateId'];

    protected function generateId(array $data)
    {
        if (empty($data['data']['id'])) {
            $data['data']['id'] = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        }
        return $data;
    }
}