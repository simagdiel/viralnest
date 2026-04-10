<?php
$senha = 'admin';
$hash = password_hash($senha, PASSWORD_BCRYPT, array('cost' => 10));
echo "Senha: admin\n";
echo "Hash: " . $hash . "\n";
echo "\nSQL:\n";
echo "UPDATE admin_users SET password='" . $hash . "', email='admin@viralnest.com' WHERE id=1;\n";
echo "\nVerificacao: " . (password_verify('admin', $hash) ? 'OK' : 'FALHOU') . "\n";
