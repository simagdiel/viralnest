<?php
// models/Course.php

class Course {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $activeOnly = true): array {
        $where = $activeOnly ? "WHERE c.is_active = 1" : "";
        return $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM modules WHERE course_id = c.id) AS module_count,
                    (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS lesson_count
             FROM courses c $where ORDER BY c.sort_order, c.created_at DESC"
        );
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM modules WHERE course_id = c.id) AS module_count,
                    (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS lesson_count
             FROM courses c WHERE c.id = ?",
            [$id]
        );
    }

    public function findBySlug(string $slug): ?array {
        return $this->db->fetchOne("SELECT * FROM courses WHERE slug = ?", [$slug]);
    }

    public function getModulesWithLessons(int $courseId): array {
        $modules = $this->db->fetchAll(
            "SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order",
            [$courseId]
        );
        foreach ($modules as &$module) {
            $module['lessons'] = $this->db->fetchAll(
                "SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order",
                [$module['id']]
            );
        }
        return $modules;
    }

    public function getLessonById(int $id): ?array {
        return $this->db->fetchOne(
            "SELECT l.*, m.course_id, m.title AS module_title FROM lessons l
             JOIN modules m ON l.module_id = m.id WHERE l.id = ?",
            [$id]
        );
    }

    // Detectar tipo de vídeo automaticamente
    public static function detectVideoType(string $url): string {
        $url = trim($url);
        if (preg_match('/youtu(be\.com|\.be)/i', $url)) return 'youtube';
        if (preg_match('/drive\.google\.com/i', $url))  return 'drive';
        if (preg_match('/vimeo\.com/i', $url))           return 'vimeo';
        if (preg_match('/^<iframe/i', $url))             return 'iframe';
        return 'iframe';
    }

    // Gerar embed HTML conforme tipo
    public static function buildEmbed(string $url, string $type): string {
        $url = trim($url);
        switch ($type) {
            case 'youtube':
                preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
                $vid = $m[1] ?? '';
                if (!$vid) return '<p>Vídeo inválido</p>';
                return '<iframe src="https://www.youtube.com/embed/' . htmlspecialchars($vid) . '" frameborder="0" allowfullscreen class="video-embed"></iframe>';

            case 'drive':
                // Converter link de compartilhamento para embed
                preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $m);
                $fileId = $m[1] ?? '';
                if (!$fileId) {
                    // Tentar extraído de /file/d/ID/view
                    preg_match('/id=([a-zA-Z0-9_-]+)/', $url, $m);
                    $fileId = $m[1] ?? '';
                }
                if (!$fileId) return '<p>URL do Google Drive inválida</p>';
                return '<iframe src="https://drive.google.com/file/d/' . htmlspecialchars($fileId) . '/preview" frameborder="0" allowfullscreen class="video-embed"></iframe>';

            case 'vimeo':
                preg_match('/vimeo\.com\/(\d+)/', $url, $m);
                $vid = $m[1] ?? '';
                if (!$vid) return '<p>Vídeo Vimeo inválido</p>';
                return '<iframe src="https://player.vimeo.com/video/' . htmlspecialchars($vid) . '" frameborder="0" allowfullscreen class="video-embed"></iframe>';

            case 'iframe':
                // Se for código iframe bruto, retornar diretamente (sanitizado)
                if (preg_match('/^<iframe/i', $url)) {
                    // Adicionar classe ao iframe existente
                    return preg_replace('/<iframe/', '<iframe class="video-embed"', $url, 1);
                }
                return '<iframe src="' . htmlspecialchars($url) . '" frameborder="0" allowfullscreen class="video-embed"></iframe>';

            default:
                return '<p>Formato de vídeo não suportado</p>';
        }
    }

    public function userHasAccess(int $userId, int $courseId): bool {
        // Acesso direto comprado ou via pontos
        $direct = $this->db->fetchOne(
            "SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?",
            [$userId, $courseId]
        );
        if ($direct) return true;

        // Curso gratuito
        $course = $this->findById($courseId);
        if ($course && $course['is_free']) return true;

        // Acesso via plano
        $planAccess = $this->db->fetchOne(
            "SELECT pca.course_id FROM plan_course_access pca
             JOIN user_subscriptions us ON us.plan_id = pca.plan_id
             WHERE us.user_id = ? AND pca.course_id = ? AND us.status = 'active'",
            [$userId, $courseId]
        );
        return (bool)$planAccess;
    }

    public function grantAccess(int $userId, int $courseId, string $accessType, string $gateway = null, float $price = 0, int $points = 0): void {
        $exists = $this->db->fetchOne("SELECT id FROM user_courses WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        if (!$exists) {
            $this->db->insert('user_courses', [
                'user_id'      => $userId,
                'course_id'    => $courseId,
                'access_type'  => $accessType,
                'gateway'      => $gateway,
                'price_paid'   => $price,
                'points_spent' => $points,
            ]);
        }
    }

    public function getDiscountedPrice(float $originalPrice, float $discountPercent): float {
        return round($originalPrice * (1 - $discountPercent / 100), 2);
    }

    public function markLessonComplete(int $userId, int $lessonId): bool {
        $exists = $this->db->fetchOne("SELECT id FROM lesson_progress WHERE user_id = ? AND lesson_id = ?", [$userId, $lessonId]);
        if ($exists && $exists['completed']) return false;

        if ($exists) {
            $this->db->update('lesson_progress', ['completed' => 1, 'completed_at' => date('Y-m-d H:i:s')], 'id = ?', [$exists['id']]);
        } else {
            $this->db->insert('lesson_progress', [
                'user_id'      => $userId,
                'lesson_id'    => $lessonId,
                'completed'    => 1,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $lesson = $this->getLessonById($lessonId);
        if ($lesson && $lesson['points_reward'] > 0) {
            $userModel = new User();
            $userModel->addPoints($userId, $lesson['points_reward'], 'complete_lesson', 'Aula concluída: ' . $lesson['title'], $lessonId);
        }
        return true;
    }

    public function getUserProgress(int $userId, int $courseId): array {
        $total = (int)($this->db->fetchOne(
            "SELECT COUNT(*) c FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ?",
            [$courseId]
        )['c'] ?? 0);

        $completed = (int)($this->db->fetchOne(
            "SELECT COUNT(*) c FROM lesson_progress lp
             JOIN lessons l ON lp.lesson_id = l.id
             JOIN modules m ON l.module_id = m.id
             WHERE m.course_id = ? AND lp.user_id = ? AND lp.completed = 1",
            [$courseId, $userId]
        )['c'] ?? 0);

        return [
            'total'     => $total,
            'completed' => $completed,
            'percent'   => $total > 0 ? (int)($completed / $total * 100) : 0,
        ];
    }

    public function create(array $data): int {
        $data['slug'] = $this->makeSlug($data['title']);
        return $this->db->insert('courses', $data);
    }

    public function makeSlug(string $title): string {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $title)));
        $base = trim($slug, '-');
        $slug = $base;
        $i = 1;
        while ($this->db->fetchOne("SELECT id FROM courses WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
