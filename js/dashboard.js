(function () {
  var toggle = document.querySelector('.navbar__toggle');
  var menu = document.getElementById('navbar-menu');
  if (!toggle || !menu) return;
 
  function setOpen(open) {
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
  }
 
  toggle.addEventListener('click', function () {
    setOpen(toggle.getAttribute('aria-expanded') !== 'true');
  });
 
  // Tutup saat link diklik
  menu.addEventListener('click', function (e) {
    if (e.target.closest('a')) setOpen(false);
  });
 
  // Tutup dengan tombol Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { setOpen(false); toggle.focus(); }
  });
 
  // Tutup saat klik di luar navbar
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.navbar')) setOpen(false);
  });
 
  // Reset saat layar melebar ke desktop
  window.matchMedia('(min-width: 900px)').addEventListener('change', function (e) {
    if (e.matches) setOpen(false);
  });
})();