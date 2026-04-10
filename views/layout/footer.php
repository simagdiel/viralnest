<?php
// views/layout/footer.php
$footerText = Setting::get('footer_text', '© 2025 ViralNest. Todos os direitos reservados.');
$fbUrl = Setting::get('facebook_url', '');
$igUrl = Setting::get('instagram_url', '');
$ytUrl = Setting::get('youtube_url', '');
?>
    </div><!-- .page-content -->
  </div><!-- .main-content -->
</div><!-- .app-layout -->

<style>
.notif-dot { display: block !important; }
#notifDropdown.open { display: block !important; }
</style>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
