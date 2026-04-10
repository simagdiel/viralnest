<?php
// models/Setting.php

class Setting {
    private static array $cache = [];
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function get(string $key, $default = null) {
        if (isset(self::$cache[$key])) return self::$cache[$key];
        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = ?", [$key]);
        $value = $row ? $row['setting_value'] : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public static function set(string $key, $value): void {
        $db = Database::getInstance();
        $db->query("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        self::$cache[$key] = $value;
    }

    public static function getAll(): array {
        $db = Database::getInstance();
        $rows = $db->fetchAll("SELECT * FROM system_settings ORDER BY category, setting_key");
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row;
        }
        return $result;
    }

    public static function getByCategory(string $category): array {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM system_settings WHERE category = ? ORDER BY setting_key", [$category]);
    }

    public static function bool(string $key, bool $default = false): bool {
        $val = self::get($key);
        if ($val === null) return $default;
        return in_array(strtolower($val), ['true', '1', 'yes', 'on']);
    }

    public static function int(string $key, int $default = 0): int {
        return (int)(self::get($key) ?? $default);
    }

    public static function clearCache(): void {
        self::$cache = [];
    }

    // Substituir variáveis em templates de mensagem
    public static function parseTemplate(string $template, array $vars = []): string {
        foreach ($vars as $k => $v) {
            $template = str_replace('{' . $k . '}', $v, $template);
        }
        $template = str_replace('{site_name}', self::get('site_name', 'ViralNest'), $template);
        return $template;
    }
}
