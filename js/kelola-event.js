const baris = document.querySelectorAll("#isi-tabel tr");
const cari = document.getElementById("cari");
const fStatus = document.getElementById("f-status");
const kosong = document.getElementById("kosong");

function saring() {
  const kata = cari.value.trim().toLowerCase();
  let tampil = 0;

  baris.forEach((b) => {
    if (!b.isConnected) return;
    const cocok =
      b.dataset.nama.toLowerCase().includes(kata) &&
      (!fStatus.value || b.dataset.status === fStatus.value);
    b.hidden = !cocok;
    if (cocok) tampil++;
  });

  kosong.hidden = tampil !== 0;
}

cari.addEventListener("input", saring);
fStatus.addEventListener("change", saring);

// ===== konfirmasi hapus =====
const dialog = document.getElementById("dialog-hapus");
const teksHapus = document.getElementById("teks-hapus");
let barisTerpilih = null;

document.querySelectorAll(".aksi-hapus").forEach((tombol) => {
  tombol.addEventListener("click", () => {
    barisTerpilih = tombol.closest("tr");
    const jumlah = Number(barisTerpilih.dataset.pendaftar);

    teksHapus.textContent =
      "Event \"" + barisTerpilih.dataset.nama + "\" akan dihapus" +
      (jumlah > 0
        ? ", beserta data " + jumlah + " pendaftar di dalamnya."
        : ".") +
      " Tindakan ini tidak bisa dibatalkan.";

    dialog.showModal();
  });
});

document.getElementById("batal-hapus").addEventListener("click", () => dialog.close());

document.getElementById("ya-hapus").addEventListener("click", () => {
  // sementara hanya tampilan; nanti PHP yang menghapus dari database
  if (barisTerpilih) barisTerpilih.remove();
  barisTerpilih = null;
  dialog.close();
  saring();
});