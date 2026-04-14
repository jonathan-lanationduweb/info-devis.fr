<?php

/**
 * Configuration Sécurité
 * InfoDevis.fr
 */

// ── Sessions sécurisées ────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure',   0); // 1 en HTTPS prod
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime',  3600);
session_name('INFODEVIS_SESSION');

// ── Constantes sécurité ────────────────────────────────────────
define('CSRF_TOKEN_LENGTH', 32);
define('BCRYPT_COST',       12);
define('JWT_SECRET',        'CHANGEZ_CE_SECRET_EN_PRODUCTION_32CHARS');
define('JWT_EXPIRY',        3600); // 1h
define('API_RATE_LIMIT',    100);  // req/min

class Security
{

    // ── CSRF ──────────────────────────────────────────────────
    public static function generateCsrf(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::generateCsrf() . '">';
    }

    // ── XSS ───────────────────────────────────────────────────
    public static function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags((string)$data), ENT_QUOTES, 'UTF-8');
    }

    public static function e(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // ── Mots de passe ─────────────────────────────────────────
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    // ── JWT API ───────────────────────────────────────────────
    public static function generateJwt(array $payload): string
    {
        $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['exp'] = time() + JWT_EXPIRY;
        $payload['iat'] = time();
        $payloadB64 = base64url_encode(json_encode($payload));
        $sig = hash_hmac('sha256', "{$header}.{$payloadB64}", JWT_SECRET, true);
        return "{$header}.{$payloadB64}." . base64url_encode($sig);
    }

    public static function verifyJwt(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payloadB64, $sig] = $parts;
        $expectedSig = base64url_encode(hash_hmac('sha256', "{$header}.{$payloadB64}", JWT_SECRET, true));
        if (!hash_equals($expectedSig, $sig)) return null;
        $payload = json_decode(base64url_decode($payloadB64), true);
        if (!$payload || $payload['exp'] < time()) return null;
        return $payload;
    }

    // ── IP ────────────────────────────────────────────────────
    public static function getIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // ── Rate limiting simple ──────────────────────────────────
    public static function checkRateLimit(string $key, int $limit = 100, int $window = 60): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $sessionKey = 'rl_' . md5($key);
        $now = time();
        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset' => $now + $window];
        }
        if ($now > $_SESSION[$sessionKey]['reset']) {
            $_SESSION[$sessionKey] = ['count' => 0, 'reset' => $now + $window];
        }
        $_SESSION[$sessionKey]['count']++;
        return $_SESSION[$sessionKey]['count'] <= $limit;
    }

    // ── Headers sécurité ──────────────────────────────────────
    public static function setSecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header(
            "Content-Security-Policy: " .
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' " .
                "https://cdn.tailwindcss.com " .
                "https://js.stripe.com " .
                "https://maps.googleapis.com; " .
                "style-src 'self' 'unsafe-inline' " .
                "https://fonts.googleapis.com " .
                "https://cdn.tailwindcss.com; " .
                "font-src 'self' " .
                "https://fonts.gstatic.com; " .
                "img-src 'self' data: https:; " .
                "frame-src https://js.stripe.com; " .
                "connect-src 'self' https://cdn.tailwindcss.com;"
        );
    }

    // ── Validation email ──────────────────────────────────────
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // ── Validation SIRET ──────────────────────────────────────
    public static function isValidSiret(string $siret): bool
    {
        $siret = preg_replace('/\s/', '', $siret);
        if (!preg_match('/^\d{14}$/', $siret)) return false;
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $n = (int)$siret[$i];
            if ($i % 2 === 0) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
        }
        return $sum % 10 === 0;
    }

    // ── Anti-doublon lead ─────────────────────────────────────
    public static function generateLeadHash(string $email, string $phone, int $catId, string $ville): string
    {
        return hash('sha256', strtolower($email) . $phone . $catId . strtolower($ville));
    }

    // ── Log sécurité ──────────────────────────────────────────
    public static function logSecurity(string $event, array $data = []): void
    {
        $logFile = LOGS_PATH . '/security_' . date('Y-m') . '.log';
        $line = date('Y-m-d H:i:s') . ' | ' . self::getIp() . ' | ' . $event . ' | ' . json_encode($data) . PHP_EOL;
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}

// ── Helpers JWT base64url ──────────────────────────────────────
function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64url_decode(string $data): string
{
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
}
