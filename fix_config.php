<?php
// fix_config.php - APAGUE APOS USAR
// Corrige BASE_URL e BASE_PATH no config.php
// Acesse: https://americaagrofer.com.br/viral/fix_config.php

header('Content-Type: text/html; charset=utf-8');

$configFile = __DIR__ . '/config/config.php';

// Detectar valores corretos automaticamente
$scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
// SCRIPT_NAME = /viral/fix_config.php → basePath = /viral
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$baseUrl  = $scheme . '://' . $host . $basePath;

echo '<style>body{font-family:sans-serif;background:#0F172A;color:#F1F5F9;padding:2rem;}
code{background:#1E293B;padding:2px 8px;border-radius:4px;color:#FBBF24;}
.ok{color:#4ADE80;} .err{color:#f87171;}</style>';

echo '<h2 style="color:#F59E0B;">&#128295; Corretor de config.php</h2>';
echo '<p>BASE_URL detectado: <code>' . htmlspecialchars($baseUrl) . '</code></p>';
echo '<p>BASE_PATH detectado: <code>' . htmlspecialchars($basePath) . '</code></p>';

if (!file_exists($configFile)) {
    echo '<p class="err">config/config.php nao encontrado!</p>';
    exit;
}

$content = file_get_contents($configFile);

// Mostrar valores atuais
preg_match("/define\('BASE_URL',\s*'([^']+)'\)/", $content, $mUrl);
preg_match("/define\('BASE_PATH',\s*'([^']+)'\)/", $content, $mPath);
echo '<p>BASE_URL atual: <code>' . htmlspecialchars($mUrl[1] ?? 'nao encontrado') . '</code></p>';
echo '<p>BASE_PATH atual: <code>' . htmlspecialchars($mPath[1] ?? 'nao encontrado') . '</code></p>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Substituir BASE_URL
    $content = preg_replace(
        "/define\('BASE_URL',\s*'[^']*'\);/",
        "define('BASE_URL', '" . addslashes($baseUrl) . "');",
        $content
    );
    // Substituir BASE_URL_RAW
    $content = preg_replace(
        "/define\('BASE_URL_RAW',\s*'[^']*'\);/",
        "define('BASE_URL_RAW', '" . addslashes($baseUrl) . "');",
        $content
    );
    // Substituir BASE_PATH
    $content = preg_replace(
        "/define\('BASE_PATH',\s*'[^']*'\);/",
        "define('BASE_PATH', '" . addslashes($basePath) . "');",
        $content
    );

    if (file_put_contents($configFile, $content)) {
        echo '<p class="ok">&#10003; config.php corrigido com sucesso!</p>';
        echo '<p><strong>Novos valores:</strong></p>';
        echo '<p>BASE_URL: <code>' . htmlspecialchars($baseUrl) . '</code></p>';
        echo '<p>BASE_PATH: <code>' . htmlspecialchars($basePath) . '</code></p>';
        echo '<p style="color:#FCD34D;margin-top:1rem;">&#9888; Apague este arquivo agora!</p>';
        echo '<p><a href="' . htmlspecialchars($baseUrl) . '/admin/login" style="color:#F59E0B;">Ir para o admin &rarr;</a></p>';
    } else {
        echo '<p class="err">Erro ao salvar config.php. Verifique permissoes da pasta config/.</p>';
    }
} else {
    echo '<br><form method="POST">
    <button type="submit" style="padding:0.8rem 2rem;background:#F59E0B;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;color:#000;">
        Corrigir config.php agora
    </button>
    </form>';
}
