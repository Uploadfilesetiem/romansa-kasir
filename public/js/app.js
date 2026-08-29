// Util kecil yang dipakai di semua halaman: toast otomatis hilang & format rupiah.
function formatRupiah(angka) {
  angka = Math.round(Number(angka) || 0);
  return 'Rp' + angka.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toast').forEach((el) => {
    setTimeout(() => el.remove(), 3000);
  });

  // Konfirmasi sebelum hapus/batalkan (dipakai lintas halaman)
  document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (!confirm(form.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    });
  });

  // Bottom sheet generik: elemen dengan [data-open-sheet="id"] membuka
  // elemen bersheet id="sheet-id", elemen [data-close-sheet] menutupnya.
  document.querySelectorAll('[data-open-sheet]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const sheet = document.getElementById('sheet-' + btn.dataset.openSheet);
      if (sheet) sheet.classList.add('open');
    });
  });
  document.querySelectorAll('[data-close-sheet]').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.closest('.sheet-root').classList.remove('open');
    });
  });
});
