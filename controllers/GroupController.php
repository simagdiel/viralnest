<?php
// controllers/GroupController.php

class GroupController {
    private Database $db;
    private User $userModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->userModel = new User();
    }

    public function index(): void {
        $user      = Auth::user();
        $pageTitle = 'Grupos';
        $groups    = $this->db->fetchAll(
            "SELECT g.*, u.name AS leader_name, COUNT(gm.id) AS member_count
             FROM groups g JOIN users u ON u.id = g.leader_id
             LEFT JOIN group_members gm ON gm.group_id = g.id
             WHERE g.status = 'active'
             GROUP BY g.id ORDER BY member_count DESC"
        );
        $myGroups  = $this->db->fetchAll(
            "SELECT g.* FROM group_members gm JOIN groups g ON g.id = gm.group_id WHERE gm.user_id = ?",
            [$user['id']]
        );
        $minPoints = Setting::int('min_points_create_group', 1000);
        $canCreate = $user['points'] >= $minPoints;
        $maxGroups = Setting::int('max_groups_per_user', 3);
        $myGroupCount = count($myGroups);

        include BASE_PATH_DIR . '/views/groups/index.php';
    }

    public function create(): void {
        $user = Auth::user();
        $minPoints = Setting::int('min_points_create_group', 1000);

        if ($user['points'] < $minPoints) {
            Auth::flash('warning', "Você precisa de {$minPoints} pontos para criar um grupo.");
            header('Location: ' . BASE_URL . '/groups');
            exit;
        }

        $maxGroups = Setting::int('max_groups_per_user', 3);
        $myCount   = (int)($this->db->fetchOne(
            "SELECT COUNT(*) c FROM group_members WHERE user_id = ?",
            [$user['id']]
        )['c'] ?? 0);

        if ($myCount >= $maxGroups) {
            Auth::flash('warning', "Você atingiu o limite de {$maxGroups} grupos.");
            header('Location: ' . BASE_URL . '/groups');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::csrfCheck();
            $name      = trim($_POST['name'] ?? '');
            $desc      = trim($_POST['description'] ?? '');
            $isPrivate = isset($_POST['is_private']) ? 1 : 0;

            if (strlen($name) < 3) {
                Auth::flash('danger', 'Nome do grupo muito curto.');
                header('Location: ' . BASE_URL . '/groups/create');
                exit;
            }

            // Criar slug único
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
            $slug = trim($slug, '-');
            $baseSlug = $slug; $i = 1;
            while ($this->db->fetchOne("SELECT id FROM groups WHERE slug = ?", [$slug])) {
                $slug = $baseSlug . '-' . $i++;
            }

            $groupId = $this->db->insert('groups', [
                'name'       => $name,
                'slug'       => $slug,
                'description'=> $desc,
                'leader_id'  => $user['id'],
                'is_private' => $isPrivate,
            ]);

            // Adicionar líder como membro
            $this->db->insert('group_members', [
                'group_id' => $groupId,
                'user_id'  => $user['id'],
                'role'     => 'moderator',
            ]);

            Auth::flash('success', 'Grupo criado com sucesso!');
            header('Location: ' . BASE_URL . '/groups/' . $groupId);
            exit;
        }

        $pageTitle = 'Criar Grupo';
        include BASE_PATH_DIR . '/views/groups/create.php';
    }

    public function show(int $id): void {
        $user  = Auth::user();
        $group = $this->db->fetchOne(
            "SELECT g.*, u.name AS leader_name FROM groups g JOIN users u ON u.id = g.leader_id WHERE g.id = ?",
            [$id]
        );
        if (!$group) {
            Auth::flash('danger', 'Grupo não encontrado.');
            header('Location: ' . BASE_URL . '/groups');
            exit;
        }

        $pageTitle = $group['name'];
        $isMember  = (bool)$this->db->fetchOne("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?", [$id, $user['id']]);
        $members   = $this->db->fetchAll(
            "SELECT u.id, u.name, u.avatar, u.level, u.points, gm.role, gm.joined_at
             FROM group_members gm JOIN users u ON u.id = gm.user_id
             WHERE gm.group_id = ? ORDER BY gm.role DESC, gm.joined_at ASC",
            [$id]
        );
        include BASE_PATH_DIR . '/views/groups/show.php';
    }

    public function join(int $id): void {
        Auth::csrfCheck();
        $user  = Auth::user();
        $group = $this->db->fetchOne("SELECT * FROM groups WHERE id = ? AND status='active'", [$id]);

        if (!$group) {
            Auth::flash('danger', 'Grupo não encontrado.');
            header('Location: ' . BASE_URL . '/groups');
            exit;
        }

        $already = $this->db->fetchOne("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?", [$id, $user['id']]);
        if ($already) {
            Auth::flash('info', 'Você já é membro deste grupo.');
        } else {
            $this->db->insert('group_members', [
                'group_id' => $id,
                'user_id'  => $user['id'],
                'role'     => 'member',
            ]);
            Auth::flash('success', 'Você entrou no grupo!');
        }
        header('Location: ' . BASE_URL . '/groups/' . $id);
        exit;
    }
}
