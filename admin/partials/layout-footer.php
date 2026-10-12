    </main><!-- /admin-content -->
  </div><!-- /admin-main -->

</div><!-- /admin-layout -->

<script>
  // Sidebar toggle for mobile
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('adminSidebar');
  if (sidebarToggle) {
    sidebarToggle.style.display = '';
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }
  // Show mobile toggle on small screens
  if (window.innerWidth <= 900) {
    document.getElementById('sidebarToggle').style.display = 'flex';
  }
  // Auto-dismiss flash
  const flash = document.getElementById('adminFlash');
  if (flash) {
    setTimeout(() => { flash.style.opacity='0'; setTimeout(()=>flash.remove(),400); }, 5000);
    flash.style.transition = 'opacity .4s ease';
  }
  // Confirm deletes
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });
  // Image preview
  const imgInput = document.getElementById('imageInput');
  const imgPreview = document.getElementById('imgPreview');
  if (imgInput && imgPreview) {
    imgInput.addEventListener('change', () => {
      const file = imgInput.files[0];
      if (file) {
        imgPreview.src = URL.createObjectURL(file);
        imgPreview.style.display = 'block';
      }
    });
  }
</script>
</body>
</html>
