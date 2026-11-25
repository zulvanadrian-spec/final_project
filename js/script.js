const form = document.getElementById("form-daftar");
const password = document.getElementById("password");
const konfirmasi = document.getElementById("konfirmasi");
const tombol = document.getElementById("lihat");
const pesan = document.getElementById("pesan-error");

// tampilkan / sembunyikan password
tombol.addEventListener("click", () => {
    const tersembunyi = password.type === "password";
    password.type = tersembunyi ? "text" : "password";
    konfirmasi.type = tersembunyi ? "text" : "password";
    tombol.textContent = tersembunyi ? "Sembunyi" : "Lihat";
});

// cek password sama sebelum form dikirim
form.addEventListener("submit", (e) => {
    if (password.value !== konfirmasi.value) {
        e.preventDefault();
        pesan.textContent = "Password tidak sama.";
        konfirmasi.focus();
    } else {
        pesan.textContent = "";
    }
});

const radioPeran = document.querySelectorAll('input[name="peran"]');
const kolomNim = document.getElementById("kolom-nim");
const inputNim = document.getElementById("nim");
const kolomBerkas = document.getElementById("kolom-berkas");
const inputBerkas = document.getElementById("berkas");
const errorBerkas = document.getElementById("error-berkas");

const JENIS_BOLEH = ["image/png", "image/jpeg", "application/pdf"];
const UKURAN_MAKS = 2 * 1024 * 1024; // 2 MB

function cekBerkas() {
  const f = inputBerkas.files[0];
  let galat = "";

  if (f) {
    if (!JENIS_BOLEH.includes(f.type)) {
      galat = "Berkas harus berupa PNG, JPG, atau PDF.";
    } else if (f.size > UKURAN_MAKS) {
      galat = "Ukuran berkas maksimal 2 MB.";
    }
  }

  errorBerkas.textContent = galat;
  errorBerkas.hidden = galat === "";
  if (galat) inputBerkas.value = "";
}

function aturPeran() {
  const rekruter =
    document.querySelector('input[name="peran"]:checked').value === "rekruter";

  // NIM: hanya untuk mahasiswa
  kolomNim.hidden = rekruter;
  inputNim.required = !rekruter;
  inputNim.disabled = rekruter;
  if (rekruter) inputNim.value = "";

  // berkas verifikasi: hanya untuk rekruter
  kolomBerkas.hidden = !rekruter;
  inputBerkas.required = rekruter;
  inputBerkas.disabled = !rekruter;
  if (!rekruter) {
    inputBerkas.value = "";
    errorBerkas.hidden = true;
  }
}

inputBerkas.addEventListener("change", cekBerkas);
radioPeran.forEach((radio) => radio.addEventListener("change", aturPeran));
aturPeran();