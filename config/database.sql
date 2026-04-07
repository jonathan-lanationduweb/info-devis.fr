-- ============================================================
-- INFO-DEVIS.FR — BASE DE DONNÉES COMPLÈTE
-- Version: 1.0.0 | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS infodevis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE infodevis;

-- ============================================================
-- ZONES GÉOGRAPHIQUES
-- ============================================================
CREATE TABLE zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('ville','code_postal','departement','region') NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CATÉGORIES
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    image VARCHAR(255),
    meta_title VARCHAR(160),
    meta_description VARCHAR(320),
    prix_min DECIMAL(10,2),
    prix_max DECIMAL(10,2),
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SERVICES
-- ============================================================
CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT UNSIGNED NOT NULL,
    nom VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    prix_min DECIMAL(10,2),
    prix_max DECIMAL(10,2),
    duree VARCHAR(100),
    image VARCHAR(255),
    meta_title VARCHAR(160),
    meta_description VARCHAR(320),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_categorie (categorie_id),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- IMAGES CATÉGORIES (anti-doublon)
-- ============================================================
CREATE TABLE category_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    filename VARCHAR(255) NOT NULL UNIQUE,
    file_hash VARCHAR(64) NOT NULL UNIQUE,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_hash (file_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- UTILISATEURS
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('client','artisan','admin') NOT NULL DEFAULT 'client',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    email_verification_token VARCHAR(100),
    password_reset_token VARCHAR(100),
    password_reset_expires TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CONSENTEMENT RGPD
-- ============================================================
CREATE TABLE consents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NOT NULL,
    consent_type ENUM('privacy_policy','terms_of_service','marketing') NOT NULL,
    accepted TINYINT(1) DEFAULT 1,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ARTISANS
-- ============================================================
CREATE TABLE artisans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    company_name VARCHAR(200),
    siret VARCHAR(14) UNIQUE,
    siret_verified TINYINT(1) DEFAULT 0,
    description TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    address TEXT,
    radius_km INT DEFAULT 30,
    plan ENUM('gratuit','starter','pro','illimite') DEFAULT 'gratuit',
    plan_expires_at TIMESTAMP NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verification_status ENUM('pending','validated','refused') DEFAULT 'pending',
    badge_verified TINYINT(1) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    response_rate DECIMAL(5,2) DEFAULT 0,
    leads_accepted INT DEFAULT 0,
    leads_refused INT DEFAULT 0,
    last_response_time INT DEFAULT 0,
    matching_score DECIMAL(10,4) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_ville (ville),
    INDEX idx_cp (code_postal),
    INDEX idx_plan (plan),
    INDEX idx_verified (is_verified),
    INDEX idx_score (matching_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ARTISAN CATÉGORIES
-- ============================================================
CREATE TABLE artisan_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY uq_artisan_cat (artisan_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ARTISAN ZONES
-- ============================================================
CREATE TABLE artisan_zones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT UNSIGNED NOT NULL,
    zone_id INT UNSIGNED NOT NULL,
    radius_km INT DEFAULT 30,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE,
    UNIQUE KEY uq_artisan_zone (artisan_id, zone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DISPONIBILITÉS ARTISAN
-- ============================================================
CREATE TABLE availability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    status ENUM('available','unavailable') DEFAULT 'available',
    note VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    INDEX idx_artisan_date (artisan_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DOCUMENTS ARTISAN
-- ============================================================
CREATE TABLE documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT UNSIGNED NOT NULL,
    type ENUM('carte_identite','justificatif_entreprise','kbis','assurance','autre') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    status ENUM('pending','validated','refused') DEFAULT 'pending',
    admin_note VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    validated_at TIMESTAMP NULL,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    INDEX idx_artisan (artisan_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STATS ARTISAN
-- ============================================================
CREATE TABLE artisan_stats (
    artisan_id INT UNSIGNED PRIMARY KEY,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    response_rate DECIMAL(5,2) DEFAULT 0,
    leads_accepted INT DEFAULT 0,
    leads_refused INT DEFAULT 0,
    last_response_time INT DEFAULT 0,
    total_revenue DECIMAL(12,2) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEADS / DEVIS
-- ============================================================
CREATE TABLE devis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) NOT NULL UNIQUE,
    client_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category_id INT UNSIGNED NOT NULL,
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    urgency ENUM('normal','urgent','tres_urgent') DEFAULT 'normal',
    budget_min DECIMAL(10,2) NULL,
    budget_max DECIMAL(10,2) NULL,
    status ENUM('pending','sent','accepted','in_progress','completed','cancelled','refused') DEFAULT 'pending',
    chatbot_session_id VARCHAR(100) NULL,
    anti_duplicate_hash VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_ville (ville),
    INDEX idx_hash (anti_duplicate_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DEVIS MULTI-CATÉGORIES
-- ============================================================
CREATE TABLE devis_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devis_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY uq_devis_cat (devis_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LEADS (association devis <-> artisan)
-- ============================================================
CREATE TABLE leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devis_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    status ENUM('pending','accepted','refused','expired') DEFAULT 'pending',
    price_offered DECIMAL(10,2) NULL,
    note TEXT NULL,
    notified_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    UNIQUE KEY uq_lead (devis_id, artisan_id),
    INDEX idx_artisan (artisan_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ABONNEMENTS
-- ============================================================
CREATE TABLE abonnements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT UNSIGNED NOT NULL,
    plan ENUM('gratuit','starter','pro','illimite') NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    stripe_subscription_id VARCHAR(255) NULL,
    stripe_customer_id VARCHAR(255) NULL,
    status ENUM('active','cancelled','expired','trialing') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    INDEX idx_artisan (artisan_id),
    INDEX idx_plan (plan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PAIEMENTS
-- ============================================================
CREATE TABLE paiements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NULL,
    type ENUM('abonnement','lead','acompte') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    stripe_payment_intent VARCHAR(255) NULL,
    stripe_charge_id VARCHAR(255) NULL,
    status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    description VARCHAR(500),
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ACOMPTES DEVIS
-- ============================================================
CREATE TABLE deposits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devis_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    percentage DECIMAL(5,2) DEFAULT 30.00,
    status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    stripe_payment_intent VARCHAR(255),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (artisan_id) REFERENCES artisans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SIGNATURES ÉLECTRONIQUES
-- ============================================================
CREATE TABLE signatures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devis_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    signature_data LONGTEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    document_hash VARCHAR(64),
    pdf_path VARCHAR(500),
    signed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AVIS (vérifiés)
-- ============================================================
CREATE TABLE avis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    devis_id INT UNSIGNED NOT NULL,
    client_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    verified TINYINT(1) DEFAULT 1,
    admin_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (devis_id) REFERENCES devis(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    UNIQUE KEY uq_avis_devis (devis_id, client_id),
    INDEX idx_artisan (artisan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MESSAGES
-- ============================================================
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    attachment VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id),
    INDEX idx_lead (lead_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    body TEXT,
    data JSON NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- BLOG
-- ============================================================
CREATE TABLE blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NULL,
    title VARCHAR(300) NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    cover_image VARCHAR(500),
    category_id INT UNSIGNED NULL,
    status ENUM('draft','pending','published','rejected') DEFAULT 'draft',
    admin_note VARCHAR(500),
    meta_title VARCHAR(160),
    meta_description VARCHAR(320),
    views INT DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEO PAGES
-- ============================================================
CREATE TABLE seo_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('category','service','city','blog') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    slug VARCHAR(300) NOT NULL UNIQUE,
    meta_title VARCHAR(160),
    meta_description VARCHAR(320),
    h1 VARCHAR(300),
    content LONGTEXT,
    faq JSON,
    schema_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CHATBOT
-- ============================================================
CREATE TABLE chatbot_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    session_id VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    response TEXT,
    detected_categories JSON NULL,
    intent VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MOTS-CLÉS CHATBOT
-- ============================================================
CREATE TABLE problem_keywords (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(200) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    priority TINYINT UNSIGNED DEFAULT 5,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_keyword (keyword),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LOGS ACTIVITÉ
-- ============================================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity VARCHAR(100) NULL,
    entity_id INT UNSIGNED NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SCORES CLIENTS (anti-spam)
-- ============================================================
CREATE TABLE client_score (
    user_id INT UNSIGNED PRIMARY KEY,
    score INT DEFAULT 100,
    status ENUM('normal','suspect','bloque') DEFAULT 'normal',
    total_requests INT DEFAULT 0,
    cancelled_requests INT DEFAULT 0,
    invalid_emails INT DEFAULT 0,
    reports INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- FILE D'ATTENTE (QUEUE)
-- ============================================================
CREATE TABLE queue_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(100) DEFAULT 'default',
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED DEFAULT 0,
    reserved_at TIMESTAMP NULL,
    available_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_queue_available (queue, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ESTIMATIONS PRIX (IA)
-- ============================================================
CREATE TABLE price_estimations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    city VARCHAR(100),
    surface DECIMAL(8,2) NULL,
    logement_type VARCHAR(50),
    min_price DECIMAL(10,2),
    max_price DECIMAL(10,2),
    confidence_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_category_city (category_id, city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- RECOMMANDATIONS
-- ============================================================
CREATE TABLE recommendations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id INT UNSIGNED NOT NULL,
    artisan_id INT UNSIGNED NOT NULL,
    score DECIMAL(10,4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE,
    UNIQUE KEY uq_reco (client_id, artisan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- MÉTRIQUES BUSINESS
-- ============================================================
CREATE TABLE business_metrics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL UNIQUE,
    revenue DECIMAL(12,2) DEFAULT 0,
    leads_count INT DEFAULT 0,
    leads_accepted INT DEFAULT 0,
    conversion_rate DECIMAL(5,2) DEFAULT 0,
    roi DECIMAL(10,2) DEFAULT 0,
    active_artisans INT DEFAULT 0,
    new_users INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- RELANCES AUTOMATIQUES
-- ============================================================
CREATE TABLE relances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('artisan_lead','client_response','devis_signature') NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    target_user_id INT UNSIGNED NOT NULL,
    scheduled_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    status ENUM('pending','sent','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_scheduled (scheduled_at, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TOKENS JWT API
-- ============================================================
CREATE TABLE api_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    last_used_at TIMESTAMP NULL,
    revoked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES INITIALES — CATÉGORIES
-- ============================================================
INSERT INTO categories (name, slug, description, icon, prix_min, prix_max, meta_title, meta_description) VALUES
('Plomberie', 'plomberie', 'Réparation fuite, installation sanitaire, débouchage canalisations', 'wrench', 50, 5000, 'Devis Plombier - Trouvez un plombier qualifié | InfoDevis', 'Obtenez des devis gratuits de plombiers certifiés. Réparation fuite, installation, débouchage. Réponse sous 24h.'),
('Électricité', 'electricite', 'Installation électrique, mise aux normes, dépannage', 'zap', 80, 8000, 'Devis Électricien - Électricien qualifié | InfoDevis', 'Comparez des devis d\'électriciens certifiés. Installation, mise aux normes, dépannage. Gratuit et sans engagement.'),
('Peinture', 'peinture', 'Peinture intérieure et extérieure, ravalement de façade', 'paint-roller', 300, 15000, 'Devis Peintre - Peintre professionnel | InfoDevis', 'Devis peinture gratuits. Intérieur, extérieur, ravalement. Peintres professionnels certifiés.'),
('Toiture', 'toiture', 'Réparation et remplacement de toiture, zinguerie', 'home', 500, 25000, 'Devis Toiture - Couvreur professionnel | InfoDevis', 'Obtenez des devis de couvreurs qualifiés. Réparation, réfection de toiture. Gratuit sous 24h.'),
('Chauffage', 'chauffage', 'Installation et entretien chaudière, radiateurs', 'flame', 500, 12000, 'Devis Chauffage - Chauffagiste qualifié | InfoDevis', 'Devis chauffage gratuits. Installation, entretien, dépannage chaudière. Chauffagistes certifiés.'),
('Menuiserie', 'menuiserie', 'Pose de fenêtres, portes, parquet, escaliers', 'tool', 200, 20000, 'Devis Menuisier - Menuisier professionnel | InfoDevis', 'Comparez des devis de menuisiers. Fenêtres, portes, parquet. Artisans qualifiés, réponse 24h.'),
('Climatisation', 'climatisation', 'Installation et entretien climatiseur, pompe à chaleur', 'wind', 800, 6000, 'Devis Climatisation - Installateur certifié | InfoDevis', 'Devis climatisation gratuits. Installation, entretien, dépannage. Installateurs certifiés.'),
('Isolation', 'isolation', 'Isolation thermique et acoustique, combles, murs', 'layers', 1000, 20000, 'Devis Isolation - Isolateur professionnel | InfoDevis', 'Obtenez des devis isolation thermique. Combles, murs, sols. Artisans RGE certifiés.'),
('Maçonnerie', 'maconnerie', 'Construction, rénovation, mur, dalle béton', 'square', 500, 50000, 'Devis Maçonnerie - Maçon qualifié | InfoDevis', 'Devis maçonnerie gratuits. Construction, rénovation, extension. Maçons qualifiés.'),
('Carrelage', 'carrelage', 'Pose carrelage sol et mur, faïence, mosaïque', 'grid', 300, 10000, 'Devis Carrelage - Carreleur professionnel | InfoDevis', 'Comparez des devis de carreleurs. Sol, mur, salle de bain. Artisans qualifiés.'),
('Jardinage', 'jardinage', 'Entretien jardin, tonte, taille, création espaces verts', 'leaf', 50, 5000, 'Devis Jardinage - Jardinier professionnel | InfoDevis', 'Devis jardinage gratuits. Entretien, création jardin, taille. Jardiniers qualifiés.'),
('Rénovation', 'renovation', 'Rénovation complète logement, travaux tous corps état', 'refresh-cw', 5000, 100000, 'Devis Rénovation - Artisan rénovation | InfoDevis', 'Obtenez des devis rénovation. Logement, appartement, maison. Artisans qualifiés.'),
('Sécurité & Domotique', 'securite-domotique', 'Alarme, vidéosurveillance, domotique, maison connectée', 'shield', 300, 5000, 'Devis Sécurité Domotique - Installateur certifié | InfoDevis', 'Devis sécurité et domotique gratuits. Alarme, caméras, maison connectée. Installateurs certifiés.'),
('Énergies Renouvelables', 'energies-renouvelables', 'Panneaux solaires, pompe à chaleur, borne recharge', 'sun', 900, 18000, 'Devis Énergies Renouvelables - Installateur RGE | InfoDevis', 'Devis énergies renouvelables gratuits. Solaire, PAC, borne électrique. Installateurs RGE.'),
('Aménagements Extérieurs', 'amenagements-exterieurs', 'Piscine, pergola, véranda, terrasse, clôture', 'umbrella', 150, 40000, 'Devis Aménagement Extérieur - Artisan qualifié | InfoDevis', 'Devis aménagement extérieur. Piscine, pergola, terrasse. Artisans qualifiés.'),
('Services B2B', 'services-b2b', 'Nettoyage industriel, maintenance IT, câblage réseau', 'briefcase', 25, 5000, 'Devis Services Entreprise B2B | InfoDevis', 'Devis services professionnels. Nettoyage, maintenance, réseau informatique.'),
('Déménagement & Services', 'demenagement-services', 'Déménagement, débarras, garde-meuble, nettoyage', 'truck', 150, 2500, 'Devis Déménagement - Déménageurs professionnels | InfoDevis', 'Comparez des devis de déménageurs. Déménagement, débarras, garde-meuble.'),
('Traitement & Protection', 'traitement-protection', 'Humidité, nuisibles, termites, mérule, dératisation', 'shield-alert', 100, 5000, 'Devis Traitement Humidité & Nuisibles | InfoDevis', 'Devis traitement humidité et nuisibles. Dératisation, termites, mérule. Experts certifiés.');

-- ============================================================
-- MOTS-CLÉS CHATBOT
-- ============================================================
INSERT INTO problem_keywords (keyword, category_id, priority) VALUES
('fuite', 1, 10), ('fuite eau', 1, 10), ('canalisation', 1, 9), ('robinet', 1, 8),
('évier', 1, 7), ('wc bouché', 1, 8), ('plombier', 1, 10), ('tuyau', 1, 8),
('câblage', 2, 9), ('prise', 2, 8), ('disjoncteur', 2, 9), ('électricien', 2, 10),
('tableau électrique', 2, 9), ('court-circuit', 2, 10), ('mise aux normes', 2, 8),
('plafond noir', 3, 8), ('peindre', 3, 9), ('peinture', 3, 9), ('repeindre', 3, 9),
('ravalement', 3, 8), ('façade', 3, 8), ('toiture', 4, 10), ('toit', 4, 9),
('tuile', 4, 9), ('fuite toit', 4, 10), ('couvreur', 4, 10), ('gouttière', 4, 7),
('chaudière', 5, 10), ('radiateur', 5, 9), ('chauffage', 5, 10), ('chauffagiste', 5, 10),
('fenêtre', 6, 9), ('porte', 6, 8), ('parquet', 6, 8), ('menuisier', 6, 10),
('climatisation', 7, 10), ('clim', 7, 9), ('pompe à chaleur', 7, 9), ('climatiseur', 7, 10),
('isolation', 8, 10), ('combles', 8, 9), ('isoler', 8, 9), ('déperdition', 8, 8),
('mur porteur', 9, 9), ('béton', 9, 8), ('maçon', 9, 10), ('extension', 9, 9),
('carrelage', 10, 10), ('carreleur', 10, 10), ('faïence', 10, 8), ('sol', 10, 7),
('jardin', 11, 9), ('tonte', 11, 8), ('taille', 11, 8), ('gazon', 11, 7),
('moisissure', 18, 10), ('humidité', 18, 10), ('infiltration', 18, 9),
('nuisible', 18, 9), ('termite', 18, 9), ('punaises', 18, 10), ('cafards', 18, 9),
('alarme', 13, 9), ('caméra', 13, 9), ('domotique', 13, 9), ('interphone', 13, 8),
('panneaux solaires', 14, 10), ('photovoltaïque', 14, 9), ('solaire', 14, 9),
('borne recharge', 14, 9), ('voiture électrique', 14, 8),
('piscine', 15, 10), ('pergola', 15, 9), ('véranda', 15, 9), ('terrasse', 15, 8),
('déménagement', 17, 10), ('déménager', 17, 10), ('débarras', 17, 9);

-- Admin par défaut (mot de passe: Admin@2024!)
INSERT INTO users (email, password, role, first_name, last_name) VALUES
('admin@info-devis.fr', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uge3pN', 'admin', 'Admin', 'InfoDevis');
