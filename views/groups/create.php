<?php
// views/groups/create.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page">
  <div class="page-header">
    <h1 class="page-title">Criar Grupo</h1>
    <p class="page-subtitle">Lidere sua própria comunidade dentro da plataforma</p>
  </div>
  <div style="max-width:560px;">
    <div class="card">
      <form method="POST" action="<?= BASE_URL ?>/groups/create">
        <input type="hidden" name="csrf_token" value="<?= Auth::csrfToken() ?>">
        <div class="form-group">
          <label class="form-label">Nome do grupo *</label>
          <input type="text" name="name" class="form-control" placeholder="Ex: Empreendedores RS" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
          <label class="form-label">Descrição</label>
          <textarea name="description" class="form-control" placeholder="Sobre o grupo..." rows="4"></textarea>
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
            <input type="checkbox" name="is_private" style="width:18px;height:18px;accent-color:var(--primary);">
            <span>Grupo privado (somente por convite)</span>
          </label>
        </div>
        <div style="display:flex;gap:0.75rem;">
          <button type="submit" class="btn btn-primary">Criar grupo 🚀</button>
          <a href="<?= BASE_URL ?>/groups" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
