const form = document.getElementById("form-pendaftaran");
const konfirmasi = document.getElementById("konfirmasi");
const pesan = document.getElementById("pesan");

form.addEventListener("submit", (e) => {
  e.preventDefault();

  const dipilih = form.querySelector('input[name="id_divisi"]:checked');

  if (!dipilih) {
    pesan.textContent = "Pilih satu divisi terlebih dahulu.";
    pesan.hidden = false;
    return;
  }

  // sementara hanya tampilan; nanti PHP yang menyimpan ke database
  pesan.hidden = true;
  form.hidden = true;
  konfirmasi.hidden = false;
});