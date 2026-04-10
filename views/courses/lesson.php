<?php
// views/courses/lesson.php
include BASE_PATH_DIR . '/views/layout/header.php';
?>
<div class="page" style="padding-bottom:3rem;">
  <!-- Breadcrumb -->
  <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">
    <a href="<?= BASE_URL ?>/courses" style="color:var(--text-muted);">Cursos</a> ›
    <a href="<?= BASE_URL ?>/courses/<?= $lesson['course_id'] ?>" style="color:var(--text-muted);"><?= htmlspecialchars($course['title']) ?></a> ›
    <?= htmlspecialchars($lesson['title']) ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;">
    <!-- Player -->
    <div>
      <!-- Vídeo -->
      <?php if (!empty($lesson['video_url'])): ?>
      <div class="video-container" style="margin-bottom:1.25rem;">
        <?= $embed ?>
      </div>
      <?php endif; ?>

      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
          <div>
            <h2 style="font-family:var(--font-head);font-size:1.25rem;font-weight:700;"><?= htmlspecialchars($lesson['title']) ?></h2>
            <div style="color:var(--text-muted);font-size:0.82rem;margin-top:0.25rem;">
              <?= htmlspecialchars($lesson['module_title']) ?>
              <?php if ($lesson['duration_minutes']): ?> · <?= $lesson['duration_minutes'] ?> min<?php endif; ?>
              <?php if ($lesson['points_reward']): ?> · 🌟 <?= $lesson['points_reward'] ?> pts ao concluir<?php endif; ?>
            </div>
          </div>
          <?php if (!$isCompleted): ?>
          <button id="markLessonComplete" class="btn btn-success"
                  data-lesson-id="<?= $lesson['id'] ?>">
            ✓ Marcar como concluída
          </button>
          <?php else: ?>
          <div class="badge badge-success" style="padding:0.5rem 1rem;">✓ Concluída</div>
          <?php endif; ?>
        </div>

        <?php if ($lesson['description']): ?>
        <hr class="divider">
        <p style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($lesson['description'])) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Sidebar: lista de aulas -->
    <div style="position:sticky;top:calc(var(--topbar-h) + 1rem);max-height:85vh;overflow-y:auto;">
      <div class="card" style="padding:1rem;">
        <div class="card-title" style="font-size:0.9rem;">Conteúdo do curso</div>
        <?php foreach ($modules as $mod): ?>
        <div style="margin-bottom:0.75rem;">
          <div style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.05em;padding:0.25rem 0;margin-bottom:0.25rem;">
            <?= htmlspecialchars($mod['title']) ?>
          </div>
          <?php foreach ($mod['lessons'] as $ls): ?>
          <a href="<?= BASE_URL ?>/lessons/<?= $ls['id'] ?>"
             style="display:flex;align-items:center;gap:0.6rem;padding:0.5rem 0.6rem;border-radius:6px;text-decoration:none;color:<?= $ls['id'] == $lesson['id'] ? 'var(--primary)' : 'var(--text-muted)' ?>;background:<?= $ls['id'] == $lesson['id'] ? 'rgba(245,158,11,0.08)' : 'transparent' ?>;font-size:0.83rem;transition:all 0.15s;">
            <span style="width:18px;height:18px;border-radius:50%;background:var(--surface-2);display:grid;place-items:center;font-size:0.65rem;flex-shrink:0;">▶</span>
            <?= htmlspecialchars($ls['title']) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<meta name="csrf" content="<?= Auth::csrfToken() ?>">
<?php include BASE_PATH_DIR . '/views/layout/footer.php'; ?>
