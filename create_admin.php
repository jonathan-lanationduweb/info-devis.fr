<?php
/**
 * create_admin.php
 * Placez ce fichier dans votre dossier info-devis/
 * Accédez via : http://localhost/info-devis/create_admin.php
 * SUPPRIMEZ-LE après utilisation !
 */

// Charger la config de votre projet
$configPath = __DIR__ . '/config/database.php';
if (!file_exists($configPath)) {
    die('Erreur: config/database.php introuvable. Placez ce fichier à la racine du projet.');
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';

// ── Paramètres du compte admin à créer ──────────────────
$adminEmail    = 'admin@info-devis.fr';
$adminPassword = 'Admin1234!';
$adminFirstName = 'Admin';
$adminLastName  = 'InfoDevis';

$hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Vérifier si déjà existant
    $existing = Database::fetch('SELECT id, email, role FROM users WHERE email = ?', [$adminEmail]);

    if ($existing) {
        // Mettre à jour le rôle et valider
        Database::query(
            'UPDATE users SET role = "admin", password = ?, email_verified_at = NOW(), is_active = 1 WHERE email = ?',
            [$hash, $adminEmail]
        );
        echo "<div style='font-family:monospace;padding:20px;background:#d1fae5;border:2px solid #059669;border-radius:8px'>";
        echo "✅ Compte admin MIS À JOUR<br>";
        echo "Email: <strong>{$adminEmail}</strong><br>";
        echo "Mot de passe: <strong>{$adminPassword}</strong><br><br>";
    } else {
        // Créer le compte
        Database::insert('users', [
            'email'             => $adminEmail,
            'password'          => $hash,
            'role'              => 'admin',
            'first_name'        => $adminFirstName,
            'last_name'         => $adminLastName,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'is_active'         => 1,
        ]);
        echo "<div style='font-family:monospace;padding:20px;background:#d1fae5;border:2px solid #059669;border-radius:8px'>";
        echo "✅ Compte admin CRÉÉ<br>";
        echo "Email: <strong>{$adminEmail}</strong><br>";
        echo "Mot de passe: <strong>{$adminPassword}</strong><br><br>";
    }

    // Aussi valider tous les comptes existants
    Database::query('UPDATE users SET email_verified_at = NOW() WHERE email_verified_at IS NULL');
    Database::query('UPDATE users SET is_active = 1 WHERE is_active = 0 OR is_active IS NULL');

    echo "✅ Tous les comptes existants sont maintenant actifs et vérifiés<br><br>";
    echo "<a href='/info-devis/connexion' style='background:#0e6c48;color:white;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold'>→ Se connecter maintenant</a>";
    echo "<br><br><strong style='color:red'>⚠️ SUPPRIMEZ CE FICHIER après utilisation !</strong>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family:monospace;padding:20px;background:#fee2e2;border:2px solid #dc2626;border-radius:8px'>";
    echo "❌ Erreur: " . $e->getMessage();
    echo "</div>";
}
