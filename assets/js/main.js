// assets/js/main.js - ViralNest Frontend JS

document.addEventListener('DOMContentLoaded', () => {

  // ─── Mobile Sidebar ───
  const hamburger = document.getElementById('hamburger');
  const sidebar   = document.querySelector('.sidebar');
  const overlay   = document.querySelector('.sidebar-overlay');

  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay?.classList.toggle('open');
    });
    overlay?.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
    });
  }

  // ─── Copy invite link ───
  const copyBtns = document.querySelectorAll('[data-copy]');
  copyBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.copy;
      const el = document.querySelector(target) || document.getElementById(target);
      const text = el ? (el.value || el.textContent) : btn.dataset.copyText;
      if (!text) return;
      navigator.clipboard.writeText(text.trim()).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '✓ Copiado!';
        btn.classList.add('btn-success');
        setTimeout(() => { btn.innerHTML = orig; btn.classList.remove('btn-success'); }, 2000);
      });
    });
  });

  // ─── Progress bar animation ───
  const bars = document.querySelectorAll('.progress-bar-fill[data-width]');
  setTimeout(() => {
    bars.forEach(bar => { bar.style.width = bar.dataset.width + '%'; });
  }, 300);

  // ─── Stagger animation on metric cards ───
  document.querySelectorAll('.metric-card').forEach((card, i) => {
    card.style.animationDelay = (i * 0.07) + 's';
  });

  // ─── Animate numbers ───
  const animateNumber = (el, target) => {
    let current = 0;
    const step = Math.ceil(target / 50);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString('pt-BR');
      if (current >= target) clearInterval(timer);
    }, 20);
  };

  document.querySelectorAll('.metric-value[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count);
    if (!isNaN(target)) animateNumber(el, target);
  });

  // ─── Notifications dropdown ───
  const notifBtn = document.getElementById('notifBtn');
  const notifDropdown = document.getElementById('notifDropdown');
  if (notifBtn && notifDropdown) {
    notifBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      notifDropdown.classList.toggle('open');
      // Mark read
      fetch('/api/notifications/read', { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const dot = notifBtn.querySelector('.notif-dot');
      if (dot) dot.style.display = 'none';
    });
    document.addEventListener('click', () => notifDropdown.classList.remove('open'));
  }

  // ─── Modals ───
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = document.getElementById(btn.dataset.modal);
      if (modal) modal.classList.add('open');
    });
  });
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) backdrop.classList.remove('open');
    });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-backdrop')?.classList.remove('open');
    });
  });

  // ─── Flash auto-dismiss ───
  const flash = document.querySelector('.flash-alert');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity 0.5s';
      flash.style.opacity = '0';
      setTimeout(() => flash.remove(), 500);
    }, 4000);
  }

  // ─── Tab switching ───
  document.querySelectorAll('.tab[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
      const group = tab.dataset.group || 'default';
      document.querySelectorAll(`.tab[data-group="${group}"]`).forEach(t => t.classList.remove('active'));
      document.querySelectorAll(`.tab-panel[data-group="${group}"]`).forEach(p => p.style.display = 'none');
      tab.classList.add('active');
      const panel = document.querySelector(`.tab-panel[data-tab="${tab.dataset.tab}"][data-group="${group}"]`);
      if (panel) panel.style.display = 'block';
    });
  });

  // ─── Confirm dialogs ───
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // ─── Mark lesson complete ───
  const lessonComplete = document.getElementById('markLessonComplete');
  if (lessonComplete) {
    lessonComplete.addEventListener('click', async () => {
      const lessonId = lessonComplete.dataset.lessonId;
      const res = await fetch('/lessons/complete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ lesson_id: lessonId, csrf_token: document.querySelector('meta[name="csrf"]')?.content }),
      });
      const data = await res.json();
      if (data.success) {
        lessonComplete.disabled = true;
        lessonComplete.textContent = '✓ Concluída!';
        lessonComplete.classList.add('btn-success');
        if (data.points) {
          showToast(`+${data.points} pontos ganhos!`, 'success');
        }
        if (data.progress_bar !== undefined) {
          const bar = document.querySelector('.course-progress-fill');
          if (bar) bar.style.width = data.progress_bar + '%';
        }
      }
    });
  }

  // ─── Toast ───
  window.showToast = (msg, type = 'info') => {
    const toast = document.createElement('div');
    toast.style.cssText = `
      position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 10px; padding: 0.9rem 1.25rem;
      font-family: var(--font-body); font-size: 0.9rem;
      box-shadow: 0 8px 30px rgba(0,0,0,0.4);
      animation: fadeSlideUp 0.3s ease;
      max-width: 320px;
      display: flex; align-items: center; gap: 0.6rem;
    `;
    const colors = { success: '#4ADE80', danger: '#f87171', warning: '#FCD34D', info: '#60A5FA' };
    const icons  = { success: '✓', danger: '✕', warning: '⚠', info: 'ℹ' };
    toast.innerHTML = `<span style="color:${colors[type]};font-weight:700;">${icons[type]}</span> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; setTimeout(() => toast.remove(), 400); }, 3500);
  };

  // ─── Purchase course with points modal ───
  document.querySelectorAll('[data-buy-points]').forEach(btn => {
    btn.addEventListener('click', () => {
      const courseId = btn.dataset.courseId;
      const points   = btn.dataset.points;
      const name     = btn.dataset.name;
      const modal    = document.getElementById('buyPointsModal');
      if (modal) {
        modal.querySelector('#buyCourseName').textContent = name;
        modal.querySelector('#buyCoursePoints').textContent = points;
        modal.querySelector('#buyCourseId').value = courseId;
        modal.classList.add('open');
      }
    });
  });
});
