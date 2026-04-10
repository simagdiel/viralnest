<?php
// reset_admin.php - APAGUE ESTE ARQUIVO APOS USAR!
// Acesse: seusite.com/viral/reset_admin.php?senha=SUA_SENHA_AQUI&email=SEU_EMAIL

$senha = isset($_GET['senha']) ? $_GET['senha'] : '';
$email = isset($_GET['email']) ? $_GET['email'] : 'admin@viralnest.com';

if (empty($senha)) {
    echo '<form style="font-family:sans-serif;padding:2rem;">';
    echo '<h2>Reset Admin ViralNest</h2>';
    echo '<p>Email: <input name="email" value="admin@viralnest.com" style="padding:0.5rem;width:280px;"></p>';
    echo '<p>Nova senha: <input name="senha" type="password" style="padding:0.5rem;width:280px;"></p>';
    echo '<p><button type="submit" style="padding:0.75rem 2rem;background:#F59E0B;border:none;border-radius:8px;font-weight:bold;cursor:pointer;">Redefinir senha</button></p>';
    echo '</form>';
    exit;
}

require_once __DIR__ . '/config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE admin_users SET email = ?, password = ? WHERE id = 1");
    $stmt->execute(array($email, $hash));

    echo '<div style="font-family:sans-serif;padding:2rem;background:#0F172A;color:#F1F5F9;min-height:100vh;">';
    echo '<h2 style="color:#F59E0B;">&#10003; Senha redefinida!</h2>';
    echo '<p>Email: <strong>' . htmlspecialchars($email) . '</strong></p>';
    echo '<p>Senha: <strong>' . htmlspecialchars($senha) . '</strong></p>';
    echo '<p style="color:#f87171;margin-top:1rem;">&#9888; APAGUE o arquivo <code>reset_admin.php</code> agora!</p>';
    echo '<p><a href="/viral/admin/login" style="color:#F59E0B;">Ir para o login do admin &rarr;</a></p>';
    echo '</div>';

} catch (Exception $e) {
    echo 'Erro: ' . $e->getMessage();
}
