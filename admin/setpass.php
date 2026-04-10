<?php
// admin/setpass.php - APAGUE APOS USAR
// Acesse: seusite.com/viral/admin/setpass.php

// Sem dependencias - arquivo totalmente standalone
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $conf  = $_POST['conf']  ?? '';

    if (strlen($senha) < 4) {
        $msg = array('tipo'=>'erro', 'txt'=>'Senha precisa ter pelo menos 4 caracteres.');
    } elseif ($senha !== $conf) {
        $msg = array('tipo'=>'erro', 'txt'=>'As senhas nao conferem.');
    } else {
        $configFile = dirname(__DIR__) . '/config/config.php';
        require_once $configFile;
        try {
            $pdo = new PDO(
                'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
                DB_USER, DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            // Atualizar todos os admins ou o id=1
            $stmt = $pdo->prepare("UPDATE admin_users SET email=?, password=? WHERE id=1");
            $stmt->execute(array($email, $hash));
            // Mostrar hash gerado para confirmar
            $msg = array('tipo'=>'ok', 'txt'=>'Senha definida! Hash: ' . $hash . ' | Verificacao: ' . (password_verify($senha, $hash) ? 'OK' : 'FALHOU'));
        } catch (Exception $e) {
            $msg = array('tipo'=>'erro', 'txt'=>'Erro BD: ' . $e->getMessage());
        }
    }
}
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>body{font-family:sans-serif;background:#0F172A;color:#F1F5F9;display:grid;place-items:center;min-height:100vh;margin:0;}
.box{background:#1E293B;padding:2rem;border-radius:16px;width:100%;max-width:420px;}
h2{color:#F59E0B;margin:0 0 1.5rem;}
label{display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.3rem;margin-top:0.9rem;}
input{width:100%;background:#243447;border:1px solid rgba(255,255,255,0.1);border-radius:8px;padding:0.65rem 1rem;color:#F1F5F9;font-size:0.9rem;box-sizing:border-box;}
button{margin-top:1.25rem;width:100%;padding:0.8rem;background:#F59E0B;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;color:#000;}
.ok{background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#4ADE80;padding:0.8rem;border-radius:8px;margin-bottom:1rem;word-break:break-all;font-size:0.82rem;}
.erro{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.8rem;border-radius:8px;margin-bottom:1rem;}
.aviso{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#FCD34D;padding:0.75rem;border-radius:8px;font-size:0.82rem;margin-top:1rem;}
</style></head><body>
<div class="box">
  <h2>&#128274; Definir senha admin</h2>
  <?php if ($msg): ?>
    <div class="<?php echo $msg['tipo']; ?>"><?php echo htmlspecialchars($msg['txt']); ?></div>
    <?php if ($msg['tipo'] === 'ok'): ?>
      <a href="../login" style="display:block;text-align:center;color:#F59E0B;margin-top:1rem;">Ir para o login &rarr;</a>
      <div class="aviso">&#9888;&#65039; Apague este arquivo agora! <code>admin/setpass.php</code></div>
    <?php endif; ?>
  <?php endif; ?>
  <?php if (!$msg || $msg['tipo'] === 'erro'): ?>
  <form method="POST">
    <label>E-mail do admin</label>
    <input type="email" name="email" value="admin@viralnest.com" required>
    <label>Nova senha</label>
    <input type="password" name="senha" placeholder="minimo 4 caracteres" required>
    <label>Confirmar senha</label>
    <input type="password" name="conf" placeholder="repita a senha" required>
    <button type="submit">Definir senha</button>
  </form>
  <?php endif; ?>
</div>
</body></html>
