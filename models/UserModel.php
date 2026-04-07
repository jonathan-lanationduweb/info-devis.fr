<?php
class UserModel {

    public function findByEmail(string $email): ?array {
        return Database::fetch('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function findById(int $id): ?array {
        return Database::fetch('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public function create(array $data): int {
        return Database::insert('users', $data);
    }

    public function updateLastLogin(int $id): void {
        Database::update('users', ['last_login' => date('Y-m-d H:i:s')], ['id' => $id]);
    }

    public function findByVerificationToken(string $token): ?array {
        return Database::fetch('SELECT * FROM users WHERE email_verification_token = ?', [$token]);
    }

    public function verifyEmail(int $id): void {
        Database::update('users', [
            'email_verified_at'        => date('Y-m-d H:i:s'),
            'email_verification_token' => null,
        ], ['id' => $id]);
    }

    public function setResetToken(int $id, string $token): void {
        Database::update('users', [
            'password_reset_token'   => $token,
            'password_reset_expires' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        ], ['id' => $id]);
    }

    public function findByResetToken(string $token): ?array {
        return Database::fetch(
            'SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()',
            [$token]
        );
    }

    public function updatePassword(int $id, string $hash): void {
        Database::update('users', [
            'password'             => $hash,
            'password_reset_token' => null,
            'password_reset_expires'=> null,
        ], ['id' => $id]);
    }
}
