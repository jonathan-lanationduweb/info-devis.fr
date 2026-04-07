<?php
class CategoryModel {

    public function getMainCategories(): array {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, name ASC'
        );
    }

    public function findBySlug(string $slug): ?array {
        return Database::fetch('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    }

    public function findById(int $id): ?array {
        return Database::fetch('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public function getSubCategories(int $parentId): array {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC',
            [$parentId]
        );
    }

    public function getServices(int $categoryId): array {
        return Database::fetchAll(
            'SELECT * FROM services WHERE categorie_id = ? AND is_active = 1 ORDER BY nom ASC',
            [$categoryId]
        );
    }

    public function getImages(int $categoryId): array {
        return Database::fetchAll(
            'SELECT * FROM category_images WHERE category_id = ? ORDER BY sort_order ASC',
            [$categoryId]
        );
    }

    public function getArtisanCount(int $categoryId): int {
        $r = Database::fetch(
            'SELECT COUNT(DISTINCT a.id) as c FROM artisans a
             JOIN artisan_categories ac ON ac.artisan_id = a.id
             WHERE ac.category_id = ? AND a.is_verified = 1',
            [$categoryId]
        );
        return (int)($r['c'] ?? 0);
    }

    public function getAll(): array {
        return Database::fetchAll('SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC');
    }
}
