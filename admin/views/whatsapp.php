<?php // admin/views/whatsapp.php
$tokenVal    = Setting::get('whatsell_token','');
$enabledVal  = Setting::bool('whatsell_enabled');
?>
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">
  <div>
    <!-- Config rápida -->
    <div class="a-card a-mb-2">
      <div class="a-card-title">⚙️ Configuração rápida — Whatsell</div>
      <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1rem;">
        As configurações completas estão em <a href="<?= BASE_URL ?>/admin/settings" style="color:var(--primary);">Configurações → WhatsApp</a>.
        Aqui você pode testar a conexão.
      </p>
      <div style="padding:0.9rem;background:var(--surface-2);border-radius:var(--radius-sm);margin-bottom:1rem;font-size:0.85rem;">
        <div style="display:flex;justify-content:space-between;">
          <span style="color:var(--text-muted);">Status</span>
          <span class="a-badge <?= $enabledVal ? 'a-badge-success' : 'a-badge-danger' ?>"><?= $enabledVal ? '✓ Ativo' : '✕ Inativo' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:0.5rem;">
          <span style="color:var(--text-muted);">Token</span>
          <span><?= $tokenVal ? '••••' . substr($tokenVal, -6) : '<span style="color:#f87171">Não configurado</span>' ?></span>
        </div>
      </div>

      <form method="POST" action="<?= BASE_URL ?>/admin/whatsapp/test">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="a-form-group">
          <label class="a-form-label">Número para teste (com código país)</label>
          <input type="tel" name="phone" class="a-form-control" placeholder="5511999999999" required>
        </div>
        <div class="a-form-group">
          <label class="a-form-label">Mensagem de teste</label>
          <input type="text" name="message" class="a-form-control" value="Teste de conexão <?= htmlspecialchars(Setting::get('site_name','ViralNest')) ?> 🚀">
        </div>
        <button type="submit" class="a-btn a-btn-primary">📤 Enviar teste</button>
      </form>
    </div>

    <!-- Logs -->
    <div class="a-card">
      <div class="a-card-title">📋 Últimas 50 mensagens enviadas</div>
      <div class="a-table-wrap">
        <table class="a-table">
          <thead><tr><th>Usuário</th><th>Telefone</th><th>Evento</th><th>Status</th><th>Data</th></tr></thead>
          <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
              <td><?= htmlspecialchars($log['name'] ?? '—') ?></td>
              <td style="font-family:monospace;font-size:0.82rem;"><?= htmlspecialchars($log['phone']) ?></td>
              <td><span class="a-badge a-badge-info"><?= htmlspecialchars($log['event_type']) ?></span></td>
              <td><span class="a-badge <?= $log['status']==='sent'?'a-badge-success':($log['status']==='failed'?'a-badge-danger':'a-badge-warning') ?>"><?= $log['status'] ?></span></td>
              <td style="color:var(--text-muted);font-size:0.78rem;"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($logs)): ?><tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Nenhum registro.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Info lateral -->
  <div class="a-card">
    <div class="a-card-title">📖 Documentação Whatsell</div>
    <div style="font-size:0.83rem;color:var(--text-muted);line-height:1.8;">
      <p><strong style="color:var(--text);">Endpoint:</strong><br><code style="color:var(--primary);font-size:0.78rem;">POST https://api.whatsell.online/api/messages/send</code></p>
      <hr class="a-divider">
      <p><strong style="color:var(--text);">Headers:</strong></p>
      <code style="font-size:0.78rem;display:block;background:var(--surface-2);padding:0.5rem;border-radius:6px;margin:0.5rem 0;">
        Authorization: Bearer {token}<br>
        Content-Type: application/json
      </code>
      <hr class="a-divider">
      <p><strong style="color:var(--text);">Body:</strong></p>
      <code style="font-size:0.78rem;display:block;background:var(--surface-2);padding:0.5rem;border-radius:6px;margin:0.5rem 0;">
        {<br>
        &nbsp;"number": "5511999999999",<br>
        &nbsp;"body": "Sua mensagem"<br>
        }
      </code>
      <hr class="a-divider">
      <p><strong style="color:var(--text);">Variáveis nas mensagens:</strong></p>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:0.3rem;">
        <li><code style="color:var(--primary);">{name}</code> — Nome do usuário</li>
        <li><code style="color:var(--primary);">{level}</code> — Nível do usuário</li>
        <li><code style="color:var(--primary);">{points}</code> — Pontos</li>
        <li><code style="color:var(--primary);">{invited}</code> — Nome do convidado</li>
        <li><code style="color:var(--primary);">{product}</code> — Nome do produto</li>
        <li><code style="color:var(--primary);">{site_name}</code> — Nome da plataforma</li>
      </ul>
    </div>
  </div>
</div>
