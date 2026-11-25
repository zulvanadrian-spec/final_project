const form = document.getElementById("form-review");

if (form) {
  const catatan = document.getElementById("catatan");
  const pesan = document.getElementById("pesan");
  const konfirmasi = document.getElementById("konfirmasi");
  const teks = document.getElementById("teks-konfirmasi");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const dipilih = form.querySelector('input[name="keputusan"]:checked');

    if (!dipilih) {
      pesan.textContent = "Pilih keputusan terlebih dahulu.";
      pesan.hidden = false;
      return;
    }

    if (dipilih.value === "ditolak" && catatan.value.trim() === "") {
      pesan.textContent = "Isi catatan untuk menjelaskan alasan penolakan.";
      pesan.hidden = false;
      catatan.focus();
      return;
    }

    // sementara hanya tampilan; nanti PHP yang menyimpan Review dan memperbarui status
    teks.textContent =
      dipilih.value === "diterima"
        ? "Pendaftaran ini ditandai Diterima. Mahasiswa akan melihat status dan catatanmu di halaman Pendaftaran Saya."
        : "Pendaftaran ini ditandai Ditolak. Mahasiswa akan melihat status dan alasan penolakan di halaman Pendaftaran Saya.";

    pesan.hidden = true;
    form.hidden = true;
    konfirmasi.hidden = false;
  });
}