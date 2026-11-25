const baris = document.querySelectorAll("#isi-tabel tr");
const cari = document.getElementById("cari");
const fEvent = document.getElementById("f-event");
const fDivisi = document.getElementById("f-divisi");
const fStatus = document.getElementById("f-status");
const jumlah = document.getElementById("jumlah");
const kosong = document.getElementById("kosong");

function saring() {
  const kata = cari.value.trim().toLowerCase();
  let tampil = 0;

  baris.forEach((b) => {
    const cocok =
      b.textContent.toLowerCase().includes(kata) &&
      (!fEvent.value || b.dataset.event === fEvent.value) &&
      (!fDivisi.value || b.dataset.divisi === fDivisi.value) &&
      (!fStatus.value || b.dataset.status === fStatus.value);

    b.hidden = !cocok;
    if (cocok) tampil++;
  });

  jumlah.textContent = "Menampilkan " + tampil + " dari " + baris.length + " pendaftar";
  kosong.hidden = tampil !== 0;
}

[cari, fEvent, fDivisi, fStatus].forEach((el) => {
  el.addEventListener(el === cari ? "input" : "change", saring);
});

saring();