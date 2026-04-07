<?php
/**
 * Service de vérification SIRET
 * API : annuaire-entreprises.data.gouv.fr
 */

class SiretService {

    private const API_URL = 'https://annuaire-entreprises.data.gouv.fr/api/v1/etablissement/';
    private const TIMEOUT  = 5;

    public static function verify(string $siret): bool {
        $siret = preg_replace('/\s/', '', $siret);
        if (strlen($siret) !== 14) return false;

        $url  = self::API_URL . urlencode($siret);
        $ctx  = stream_context_create([
            'http' => [
                'timeout'        => self::TIMEOUT,
                'method'         => 'GET',
                'header'         => "User-Agent: InfoDevis/1.0\r\n",
                'ignore_errors'  => true,
            ],
            'ssl'  => ['verify_peer' => true],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            error_log('[SIRET] API non joignable pour ' . $siret);
            return true; // Fail open (ne pas bloquer si API down)
        }

        $data = json_decode($response, true);
        return isset($data['siret']) && $data['siret'] === $siret;
    }

    public static function getInfo(string $siret): ?array {
        $siret = preg_replace('/\s/', '', $siret);
        $url   = self::API_URL . urlencode($siret);
        $ctx   = stream_context_create(['http' => ['timeout' => self::TIMEOUT]]);
        $resp  = @file_get_contents($url, false, $ctx);
        if (!$resp) return null;
        $data = json_decode($resp, true);
        if (!isset($data['siret'])) return null;

        return [
            'siret'        => $data['siret'] ?? $siret,
            'company_name' => $data['unite_legale']['denomination'] ?? null,
            'address'      => ($data['adresse']['numero_voie'] ?? '')
                            . ' ' . ($data['adresse']['libelle_voie'] ?? ''),
            'city'         => $data['adresse']['libelle_commune'] ?? null,
            'postal_code'  => $data['adresse']['code_postal'] ?? null,
            'activity'     => $data['unite_legale']['libelle_activite_principale'] ?? null,
        ];
    }
}
