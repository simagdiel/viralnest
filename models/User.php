<?php
// models/User.php

class User {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function findByEmail(string $email): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function findByInviteCode(string $code): ?array {
        return $this->db->fetchOne("SELECT * FROM users WHERE invite_code = ?", [$code]);
    }

    public function create(array $data): int {
        $data['password']    = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['invite_code'] = $this->generateInviteCode();
        $data['points']      = Setting::int('points_register', 50);
        $data['level']       = 'explorer';
        return $this->db->insert('users', $data);
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) return null;
        if ($user['status'] !== 'active') return null;
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        return $user;
    }

    public function addPoints(int $userId, int $pts, string $actionType, string $desc = '', int $refId = null): void {
        // Aplicar multiplicador do plano
        $multiplier = $this->getPlanMultiplier($userId);
        $pts = (int)round($pts * $multiplier);

        $this->db->insert('points', [
            'user_id'      => $userId,
            'action_type'  => $actionType,
            'points'       => $pts,
            'description'  => $desc,
            'reference_id' => $refId,
        ]);

        $user = $this->findById($userId);
        $newTotal = $user['points'] + $pts;
        $newLevel = $this->calculateLevel($newTotal);

        $this->db->update('users', ['points' => $newTotal, 'level' => $newLevel], 'id = ?', [$userId]);

        // Notificar subida de nível
        if ($newLevel !== $user['level']) {
            $ws = new WhatsellService();
            $user['points'] = $newTotal;
            $ws->notifyLevelUp($user, $newLevel);
            $this->createNotification($userId, 'Você subiu de nível!', "Parabéns! Você agora é {$newLevel}!", 'level');
        }
    }

    public function spendPoints(int $userId, int $pts): bool {
        $user = $this->findById($userId);
        if (!$user || $user['points'] < $pts) return false;
        $this->db->update('users', ['points' => $user['points'] - $pts], 'id = ?', [$userId]);
        $this->db->insert('points', [
            'user_id'     => $userId,
            'action_type' => 'spend',
            'points'      => -$pts,
            'description' => 'Troca por produto/curso',
        ]);
        return true;
    }

    public function calculateLevel(int $points): string {
        $levels = [
            'legend'   => Setting::int('level_legend_min',   7000),
            'master'   => Setting::int('level_master_min',   3000),
            'guardian' => Setting::int('level_guardian_min', 1000),
            'mentor'   => Setting::int('level_mentor_min',    200),
            'explorer' => 0,
        ];
        foreach ($levels as $level => $min) {
            if ($points >= $min) return $level;
        }
        return 'explorer';
    }

    public function getLevelProgress(array $user): array {
        $levels = [
            'explorer' => ['next' => 'mentor',   'max' => Setting::int('level_mentor_min',    200)],
            'mentor'   => ['next' => 'guardian', 'max' => Setting::int('level_guardian_min', 1000)],
            'guardian' => ['next' => 'master',   'max' => Setting::int('level_master_min',   3000)],
            'master'   => ['next' => 'legend',   'max' => Setting::int('level_legend_min',   7000)],
            'legend'   => ['next' => null,        'max' => Setting::int('level_legend_min',   7000)],
        ];
        $current = $user['level'];
        $info    = $levels[$current] ?? $levels['explorer'];
        $prevMin = $this->getLevelMin($current);
        $range   = $info['max'] - $prevMin;
        $progress = $range > 0 ? min(100, (int)(($user['points'] - $prevMin) / $range * 100)) : 100;
        return [
            'current'    => $current,
            'next'       => $info['next'],
            'max'        => $info['max'],
            'points'     => $user['points'],
            'progress'   => $progress,
            'points_needed' => max(0, $info['max'] - $user['points']),
        ];
    }

    private function getLevelMin(string $level): int {
        if ($level === 'mentor')   return Setting::int('level_mentor_min',    200);
        if ($level === 'guardian') return Setting::int('level_guardian_min', 1000);
        if ($level === 'master')   return Setting::int('level_master_min',   3000);
        if ($level === 'legend')   return Setting::int('level_legend_min',   7000);
        return 0;
    }

    public function generateInviteCode(): string {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $exists = $this->db->fetchOne("SELECT id FROM users WHERE invite_code = ?", [$code]);
        } while ($exists);
        return $code;
    }

    public function getRanking(int $limit = 50): array {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.avatar, u.points, u.level,
                    (SELECT COUNT(*) FROM users WHERE invited_by = u.id) AS invite_count,
                    (SELECT name FROM plans p JOIN user_subscriptions us ON us.plan_id = p.id WHERE us.user_id = u.id AND us.status = 'active' ORDER BY p.price DESC LIMIT 1) AS plan_name
             FROM users u WHERE u.status = 'active' ORDER BY u.points DESC LIMIT ?",
            [$limit]
        );
    }

    public function getInvitedUsers(int $userId): array {
        return $this->db->fetchAll(
            "SELECT id, name, avatar, points, level, created_at FROM users WHERE invited_by = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    public function getActivePlan(int $userId): ?array {
        return $this->db->fetchOne(
            "SELECT p.*, us.expires_at, us.status AS sub_status
             FROM user_subscriptions us JOIN plans p ON p.id = us.plan_id
             WHERE us.user_id = ? AND us.status = 'active' ORDER BY p.price DESC LIMIT 1",
            [$userId]
        );
    }

    public function getPlanMultiplier(int $userId): float {
        $plan = $this->getActivePlan($userId);
        return $plan ? (float)$plan['points_multiplier'] : 1.0;
    }

    public function getCourseDiscount(int $userId): float {
        // Desconto por nível de gamificação
        $user = $this->findById($userId);
        $lvl = $user['level'];
        if ($lvl === 'mentor')        $levelDiscount = 5;
        elseif ($lvl === 'guardian')  $levelDiscount = 10;
        elseif ($lvl === 'master')    $levelDiscount = 20;
        elseif ($lvl === 'legend')    $levelDiscount = 30;
        else                          $levelDiscount = 0;
        // Desconto do plano (pega o maior)
        $plan = $this->getActivePlan($userId);
        $planDiscount = $plan ? (float)$plan['course_discount'] : 0;
        return max($levelDiscount, $planDiscount);
    }

    public function createNotification(int $userId, string $title, string $message, string $type = 'system'): void {
        $this->db->insert('notifications', [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);
    }

    public function getNotifications(int $userId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function countUnreadNotifications(int $userId): int {
        $r = $this->db->fetchOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
        return (int)($r['c'] ?? 0);
    }

    public function markNotificationsRead(int $userId): void {
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }

    public function getStats(): array {
        return [
            'total'   => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users")['c'] ?? 0),
            'active'  => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users WHERE status='active'")['c'] ?? 0),
            'today'   => (int)($this->db->fetchOne("SELECT COUNT(*) c FROM users WHERE DATE(created_at)=CURDATE()")['c'] ?? 0),
            'points_issued' => (int)($this->db->fetchOne("SELECT COALESCE(SUM(points),0) c FROM points WHERE points > 0")['c'] ?? 0),
        ];
    }
}
