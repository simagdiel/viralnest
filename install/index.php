<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ViralNest — Instalador</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap');
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:#0F172A;color:#F1F5F9;min-height:100vh;display:grid;place-items:center;padding:1.5rem}
.card{background:#1E293B;border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:2.5rem;width:100%;max-width:540px;box-shadow:0 4px 40px rgba(0,0,0,0.4)}
.logo{text-align:center;margin-bottom:2rem}
.logo h1{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:#F59E0B;letter-spacing:-0.03em}
.logo p{color:#94A3B8;font-size:0.9rem;margin-top:0.25rem}
.steps{display:flex;gap:0;margin-bottom:2rem;border-radius:99px;overflow:hidden;background:#243447}
.step{flex:1;text-align:center;padding:0.5rem;font-size:0.7rem;font-weight:600;color:#64748B;transition:all .2s}
.step.active{background:linear-gradient(135deg,#F59E0B,#FBBF24);color:#000}
.step.done{background:#1E3A5F;color:#60A5FA}
.form-group{margin-bottom:1rem}
label{display:block;font-size:0.82rem;color:#94A3B8;margin-bottom:0.35rem;font-weight:500}
input{width:100%;background:#243447;border:1px solid rgba(255,255,255,0.08);border-radius:8px;padding:0.7rem 1rem;color:#F1F5F9;font-size:0.9rem;outline:none;transition:border-color .2s;font-family:'DM Sans',sans-serif}
input:focus{border-color:#F59E0B}
.btn{display:block;width:100%;padding:0.85rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-size:1rem;font-weight:700;cursor:pointer;border:none;background:linear-gradient(135deg,#F59E0B,#FBBF24);color:#000;transition:all .2s;margin-top:1.5rem}
.btn:hover{filter:brightness(1.1)}
.alert{padding:0.8rem 1rem;border-radius:8px;font-size:0.88rem;margin-bottom:1rem}
.alert-danger{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.2);color:#f87171}
.divider{border:none;border-top:1px solid rgba(255,255,255,0.06);margin:1.25rem 0}
code{background:#0F172A;padding:0.2rem 0.5rem;border-radius:4px;font-size:0.8rem;color:#FBBF24}
</style>
</head>
<body>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) session_start();

$step  = isset($_GET['step']) ? (int)$_GET['step'] : (isset($_SESSION['install_step']) ? (int)$_SESSION['install_step'] : 1);
$error = '';

// Verificar instalacao existente - compativel PHP 7.4
$configFile  = dirname(__DIR__) . '/config/config.php';
$isInstalled = false;
if (file_exists($configFile)) {
    $cfgContent  = file_get_contents($configFile);
    $hasDefine   = strpos($cfgContent, "define('DB_HOST'") !== false;
    $isTemplate  = strpos($cfgContent, '{{DB_HOST}}') !== false;
    $isPlaceholder = strpos($cfgContent, '// Este arquivo') !== false;
    $isInstalled = $hasDefine && !$isTemplate && !$isPlaceholder;
}
if ($isInstalled && $step < 5) {
    header('Location: ../'); exit;
}

// PROCESSAR POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($step === 1) {
        $_SESSION['install_step'] = 2;
        header('Location: index.php?step=2'); exit;
    }

    if ($step === 2) {
        $host = trim(isset($_POST['db_host']) ? $_POST['db_host'] : 'localhost');
        $name = trim(isset($_POST['db_name']) ? $_POST['db_name'] : '');
        $user = trim(isset($_POST['db_user']) ? $_POST['db_user'] : '');
        $pass = isset($_POST['db_pass']) ? $_POST['db_pass'] : '';
        if (!$name || !$user) {
            $error = 'Preencha o nome do banco e o usuario.';
        } else {
            try {
                new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $_SESSION['db'] = array('host'=>$host,'name'=>$name,'user'=>$user,'pass'=>$pass);
                $_SESSION['install_step'] = 3;
                header('Location: index.php?step=3'); exit;
            } catch (PDOException $e) {
                $error = 'Erro de conexao: ' . $e->getMessage();
            }
        }
    }

    if ($step === 3) {
        $db = isset($_SESSION['db']) ? $_SESSION['db'] : array();
        if (empty($db['name'])) {
            $error = 'Sessao expirada. Volte ao passo 2.';
        } else {
            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
                    $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $sqlFile = dirname(__DIR__) . '/database/schema.sql';
                if (!file_exists($sqlFile)) throw new Exception('database/schema.sql nao encontrado.');
                $sql = file_get_contents($sqlFile);
                // Separar por ponto-e-virgula seguido de nova linha
                $parts = explode(";\n", $sql);
                foreach ($parts as $stmt) {
                    $stmt = trim($stmt);
                    if ($stmt !== '' && substr($stmt, 0, 2) !== '--') {
                        try { $pdo->exec($stmt); } catch(Exception $ex) { /* ignora erros de IF NOT EXISTS */ }
                    }
                }
                $_SESSION['install_step'] = 4;
                header('Location: index.php?step=4'); exit;
            } catch (Exception $e) {
                $error = 'Erro ao importar: ' . $e->getMessage();
            }
        }
    }

    if ($step === 4) {
        $siteName   = trim(isset($_POST['site_name'])   ? $_POST['site_name']   : 'ViralNest');
        $siteUrl    = rtrim(trim(isset($_POST['site_url']) ? $_POST['site_url'] : ''), '/');
        $adminEmail = trim(isset($_POST['admin_email']) ? $_POST['admin_email'] : '');
        $adminPass  = isset($_POST['admin_pass']) ? $_POST['admin_pass'] : '';
        $db = isset($_SESSION['db']) ? $_SESSION['db'] : array();

        if (strlen($adminPass) < 6) {
            $error = 'Senha precisa ter no minimo 6 caracteres.';
        } elseif (empty($db['name'])) {
            $error = 'Sessao expirada. Reinicie a instalacao.';
        } else {
            try {
                $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4",
                    $db['user'], $db['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $hashed = password_hash($adminPass, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE admin_users SET email=?, password=? WHERE id=1")->execute(array($adminEmail, $hashed));
                $pdo->prepare("UPDATE system_settings SET setting_value=? WHERE setting_key='site_name'")->execute(array($siteName));
                $pdo->prepare("UPDATE system_settings SET setting_value=? WHERE setting_key='site_url'")->execute(array($siteUrl));

                // Detectar base path
                $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/install/index.php';
                $installDir = dirname($scriptName);
                $parentDir  = dirname($installDir);
                $basePath   = ($parentDir === '.' || $parentDir === '/') ? '' : rtrim($parentDir, '/');

                $encKey = bin2hex(random_bytes(24));

                $cfg  = "<?php\n";
                $cfg .= "// config/config.php - Gerado pelo instalador ViralNest em " . date('Y-m-d H:i:s') . "\n\n";
                $cfg .= "define('DB_HOST',        '" . addslashes($db['host']) . "');\n";
                $cfg .= "define('DB_NAME',        '" . addslashes($db['name']) . "');\n";
                $cfg .= "define('DB_USER',        '" . addslashes($db['user']) . "');\n";
                $cfg .= "define('DB_PASS',        '" . addslashes($db['pass']) . "');\n";
                $cfg .= "define('DB_CHARSET',     'utf8mb4');\n\n";
                $cfg .= "define('BASE_URL',       '" . addslashes($siteUrl) . "');\n";
                $cfg .= "define('BASE_URL_RAW',   '" . addslashes($siteUrl) . "');\n";
                $cfg .= "define('BASE_PATH',      '" . addslashes($basePath) . "');\n";
                $cfg .= "define('ENCRYPTION_KEY', '" . $encKey . "');\n";
                $cfg .= "define('APP_VERSION',    '1.0.0');\n\n";
                $cfg .= "define('UPLOAD_PATH', __DIR__ . '/../assets/img/uploads/');\n";
                $cfg .= "define('UPLOAD_URL',  BASE_URL . '/assets/img/uploads/');\n\n";
                $cfg .= "date_default_timezone_set('America/Sao_Paulo');\n\n";
                $cfg .= "error_reporting(0);\n";
                $cfg .= "ini_set('display_errors', 0);\n\n";
                $cfg .= "if (session_status() === PHP_SESSION_NONE) {\n";
                $cfg .= "    ini_set('session.cookie_httponly', 1);\n";
                $cfg .= "    ini_set('session.use_strict_mode', 1);\n";
                $cfg .= "    session_start();\n";
                $cfg .= "}\n";

                if (file_put_contents($configFile, $cfg) === false) {
                    throw new Exception('Nao foi possivel gravar config/config.php. Verifique permissoes da pasta config/.');
                }

                $uploadDir = dirname(__DIR__) . '/assets/img/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $_SESSION['site_name']    = $siteName;
                $_SESSION['site_url']     = $siteUrl;
                $_SESSION['admin_email']  = $adminEmail;
                $_SESSION['install_step'] = 5;
                header('Location: index.php?step=5'); exit;

            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>

<div class="card">
  <div class="logo">
    <div style="font-size:2.5rem;margin-bottom:0.5rem;">🚀</div>
    <h1>ViralNest</h1>
    <p>Instalador &mdash; Passo <?php echo $step; ?> de 5</p>
  </div>

  <div class="steps">
    <?php
    $stepNames = array('Verificacao','Banco','Importar','Configurar','Pronto!');
    foreach ($stepNames as $i => $sname) {
        $n   = $i + 1;
        $cls = ($n === $step) ? 'active' : (($n < $step) ? 'done' : '');
        echo '<div class="step ' . $cls . '">' . ($n < $step ? '&#10003; ' : '') . htmlspecialchars($sname) . '</div>';
    }
    ?>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger">&#10060; <?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if ($step === 1): ?>
    <h3 style="font-family:'Syne',sans-serif;margin-bottom:1rem;">Verificacao do servidor</h3>
    <?php
    $checks = array(
        'PHP 7.4+'               => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO MySQL'              => extension_loaded('pdo_mysql'),
        'cURL'                   => extension_loaded('curl'),
        'OpenSSL'                => extension_loaded('openssl'),
        'config/ gravavel'       => is_writable(dirname(__DIR__) . '/config/'),
        'assets/ gravavel'       => is_writable(dirname(__DIR__) . '/assets/'),
        'schema.sql existe'      => file_exists(dirname(__DIR__) . '/database/schema.sql'),
    );
    $allOk = !in_array(false, $checks, true);
    foreach ($checks as $label => $ok) {
        echo '<div style="padding:0.55rem 0;display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.06);">';
        echo '<span style="font-size:0.875rem;">' . htmlspecialchars($label) . '</span>';
        echo $ok ? '<span style="color:#4ADE80;font-weight:600;">&#10003; OK</span>' : '<span style="color:#f87171;font-weight:600;">&#10007; Falhou</span>';
        echo '</div>';
    }
    ?>
    <div style="font-size:0.75rem;color:#475569;margin-top:0.75rem;">PHP <?php echo PHP_VERSION; ?></div>
    <?php if ($allOk): ?>
      <form method="POST" action="index.php?step=1">
        <button type="submit" class="btn">Proximo &rarr;</button>
      </form>
    <?php else: ?>
      <div class="alert alert-danger" style="margin-top:1rem;">Corrija os itens marcados antes de continuar.</div>
    <?php endif; ?>

  <?php elseif ($step === 2): ?>
    <h3 style="font-family:'Syne',sans-serif;margin-bottom:1rem;">Conexao com banco de dados</h3>
    <form method="POST" action="index.php?step=2">
      <div class="form-group">
        <label>Host MySQL</label>
        <input type="text" name="db_host" value="localhost" required>
      </div>
      <div class="form-group">
        <label>Nome do banco *</label>
        <input type="text" name="db_name" placeholder="viralnest_db" required>
      </div>
      <div class="form-group">
        <label>Usuario *</label>
        <input type="text" name="db_user" placeholder="root" required>
      </div>
      <div class="form-group">
        <label>Senha</label>
        <input type="password" name="db_pass" placeholder="(deixe vazio se nao houver)">
      </div>
      <button type="submit" class="btn">Testar conexao &rarr;</button>
    </form>

  <?php elseif ($step === 3): ?>
    <h3 style="font-family:'Syne',sans-serif;margin-bottom:1rem;">Importar banco de dados</h3>
    <p style="color:#94A3B8;font-size:0.875rem;line-height:1.6;margin-bottom:1rem;">
      Serao criadas todas as tabelas no banco
      <code><?php echo htmlspecialchars(isset($_SESSION['db']['name']) ? $_SESSION['db']['name'] : ''); ?></code>.
    </p>
    <div style="background:#0F172A;border-radius:8px;padding:0.9rem;font-size:0.82rem;color:#64748B;margin-bottom:1rem;">
      Usamos <code>CREATE TABLE IF NOT EXISTS</code> &mdash; seguro em bancos existentes.
    </div>
    <form method="POST" action="index.php?step=3">
      <button type="submit" class="btn">Importar e continuar &rarr;</button>
    </form>

  <?php elseif ($step === 4): ?>
    <h3 style="font-family:'Syne',sans-serif;margin-bottom:1rem;">Configuracoes da plataforma</h3>
    <?php
    $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host2 = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    ?>
    <form method="POST" action="index.php?step=4">
      <div class="form-group">
        <label>Nome da plataforma</label>
        <input type="text" name="site_name" value="ViralNest" required maxlength="60">
      </div>
      <div class="form-group">
        <label>URL do site <span style="color:#64748B;">(sem barra no final)</span></label>
        <input type="text" name="site_url" value="<?php echo htmlspecialchars($proto.'://'.$host2); ?>" required>
      </div>
      <hr class="divider">
      <div class="form-group">
        <label>E-mail do administrador</label>
        <input type="email" name="admin_email" value="admin@viralnest.com" required>
      </div>
      <div class="form-group">
        <label>Senha do admin <span style="color:#64748B;">(minimo 6 caracteres)</span></label>
        <input type="password" name="admin_pass" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required minlength="6">
      </div>
      <button type="submit" class="btn">Finalizar instalacao &#128640;</button>
    </form>

  <?php elseif ($step === 5): ?>
    <div style="text-align:center;">
      <div style="font-size:4rem;margin-bottom:1rem;">&#127881;</div>
      <h2 style="font-family:'Syne',sans-serif;font-size:1.5rem;color:#F59E0B;margin-bottom:0.5rem;">Instalacao concluida!</h2>
      <p style="color:#94A3B8;margin-bottom:1.5rem;">Sua plataforma ViralNest esta no ar.</p>
      <?php $sUrl = isset($_SESSION['site_url']) ? $_SESSION['site_url'] : '..'; ?>
      <div style="background:#0F172A;border-radius:12px;padding:1.25rem;text-align:left;margin-bottom:1.25rem;">
        <div style="margin-bottom:0.75rem;font-size:0.875rem;">
          <div style="color:#64748B;margin-bottom:0.2rem;">Site:</div>
          <code><?php echo htmlspecialchars($sUrl); ?>/</code>
        </div>
        <div style="margin-bottom:0.75rem;font-size:0.875rem;">
          <div style="color:#64748B;margin-bottom:0.2rem;">Admin:</div>
          <code><?php echo htmlspecialchars($sUrl); ?>/admin/login</code>
        </div>
        <div style="font-size:0.875rem;">
          <div style="color:#64748B;margin-bottom:0.2rem;">Login:</div>
          <code><?php echo htmlspecialchars(isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : ''); ?></code>
        </div>
      </div>
      <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:0.9rem;font-size:0.82rem;color:#f87171;margin-bottom:1.5rem;text-align:left;">
        &#9888;&#65039; <strong>Seguranca:</strong> Exclua a pasta <code>/install/</code> apos instalar.
      </div>
      <a href="<?php echo htmlspecialchars($sUrl); ?>/"
         style="display:block;padding:0.85rem;border-radius:8px;background:linear-gradient(135deg,#F59E0B,#FBBF24);color:#000;font-weight:700;text-decoration:none;font-size:1rem;margin-bottom:0.75rem;">
        Acessar o site &rarr;
      </a>
      <a href="<?php echo htmlspecialchars($sUrl); ?>/admin/login"
         style="display:block;padding:0.75rem;border-radius:8px;background:rgba(255,255,255,0.06);color:#94A3B8;text-decoration:none;font-size:0.9rem;">
        Ir para o painel admin
      </a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
