<?php
// controllers/InviteController.php
class InviteController {
    public function index(): void {
        $user = Auth::user();
        $db   = Database::getInstance();
        $pageTitle = 'Meus Convites';
        $inviteUrl = BASE_URL . '/register?invite=' . $user['invite_code'];
        $invitedUsers = (new User())->getInvitedUsers($user['id']);
        $pointsPerInvite = Setting::int('points_invite', 100);
        include BASE_PATH_DIR . '/views/invite/index.php';
    }
}
