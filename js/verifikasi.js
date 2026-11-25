const LABEL = { menunggu: "Menunggu", disetujui: "Disetujui", ditolak: "Ditolak" };
const KELAS = { menunggu: "menunggu", disetujui: "diterima", ditolak: "ditolak" };
const ADMIN = "Nama Admin";

const tbody = document.getElementById("isi-tabel");
const baris = [...tbody.querySelectorAll("tr")];
const tabs = document.querySelectorAll(".tab");
const cari = document.getElementById("cari");
const kosong = document.getElementById("kosong");
const info = document.getElementById("info");

const dlgBerkas = document.getElementById("dialog-berkas");
const dlgTolak = document.getElementById("dialog-tolak");
const formTolak = document.getElementById("form-tolak");
const alasan = document.getElementById("alasan");
const galat = document.getElementById("galat-alasan");

let statusAktif = "menunggu";
let barisPilih = null;

function buat(tag, kelas, teks) {
  const el = document.createElement(tag);
  if (kelas) el.className = kelas;
  if (teks !== undefined) el.textContent = teks;
  return el;
}

function tombol(aksi, kelas, teks) {
  const b = buat("button", "aksi " + kelas, teks);
  b.type = "button";
  b.dataset.aksi = aksi;
  return b;
}

function tanggalIni() {
  return new Date().toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
}

// isi kolom status, keterangan, dan aksi dari atribut data-*
function gambar(tr) {
  const d = tr.dataset;
  const sel = tr.children;

  sel[3].replaceChildren(buat("span", "status " + KELAS[d.status], LABEL[d.status]));
  sel[4].replaceChildren(buat("span", "keterangan", d.keterangan || "-"));

  const aksi = [tombol("berkas", "aksi-detail", "Lihat berkas")];
  if (d.status === "menunggu") {
    aksi.push(tombol("setuju", "aksi-setuju", "Setujui"));
    aksi.push(tombol("tolak", "aksi-tolak", "Tolak"));
  }
  sel[5].replaceChildren(...aksi);

  tr.className = d.status === "ditolak" ? "ditolak" : "";
}

function hitung() {
  const n = { menunggu: 0, disetujui: 0, ditolak: 0 };
  baris.forEach((tr) => { if (tr.isConnected) n[tr.dataset.status]++; });
  document.getElementById("n-menunggu").textContent = n.menunggu;
  document.getElementById("n-disetujui").textContent = n.disetujui;
  document.getElementById("n-ditolak").textContent = n.ditolak;
  document.getElementById("n-semua").textContent = n.menunggu + n.disetujui + n.ditolak;
}

function saring() {
  const kata = cari.value.trim().toLowerCase();
  let tampil = 0;

  baris.forEach((tr) => {
    if (!tr.isConnected) return;
    const d = tr.dataset;
    const cocok =
      (!statusAktif || d.status === statusAktif) &&
      (d.nama + " " + d.email).toLowerCase().includes(kata);
    tr.hidden = !cocok;
    if (cocok) tampil++;
  });

  if (tampil === 0) {
    kosong.textContent =
      statusAktif === "menunggu" && !cari.value.trim()
        ? "Tidak ada permohonan yang menunggu verifikasi."
        : "Tidak ada permohonan yang cocok.";
  }
  kosong.hidden = tampil !== 0;
  hitung();
}

function tampilInfo(teks) {
  info.textContent = teks;
  info.hidden = false;
}

tabs.forEach((t) => {
  t.addEventListener("click", () => {
    statusAktif = t.dataset.status;
    tabs.forEach((x) => x.setAttribute("aria-pressed", String(x === t)));
    saring();
  });
});
cari.addEventListener("input", saring);

tbody.addEventListener("click", (e) => {
  const t = e.target.closest("button[data-aksi]");
  if (!t) return;

  const tr = t.closest("tr");
  const d = tr.dataset;

  if (t.dataset.aksi === "berkas") {
    document.getElementById("berkas-pemohon").textContent = d.nama + " (" + d.email + ")";
    document.getElementById("berkas-nama").textContent = d.berkas;
    document.getElementById("berkas-meta").textContent = d.jenis + " \u00B7 " + d.ukuran;
    dlgBerkas.showModal();
    return;
  }

  if (t.dataset.aksi === "setuju") {
    if (confirm('Setujui "' + d.nama + '"? Akun ini akan berubah menjadi Panitia dan bisa login.')) {
      d.status = "disetujui";
      d.keterangan = "Disetujui " + tanggalIni() + " oleh " + ADMIN;
      gambar(tr);
      tampilInfo("Akun " + d.nama + " disetujui dan kini berperan sebagai Panitia.");
      saring();
    }
    return;
  }

  if (t.dataset.aksi === "tolak") {
    barisPilih = tr;
    document.getElementById("tolak-pemohon").textContent = d.nama + " (" + d.email + ")";
    alasan.value = "";
    galat.hidden = true;
    dlgTolak.showModal();
    alasan.focus();
  }
});

formTolak.addEventListener("submit", (e) => {
  e.preventDefault();

  const teks = alasan.value.trim();
  if (teks === "") {
    galat.textContent = "Alasan penolakan wajib diisi.";
    galat.hidden = false;
    return;
  }

  barisPilih.dataset.status = "ditolak";
  barisPilih.dataset.keterangan = teks;
  gambar(barisPilih);
  dlgTolak.close();
  tampilInfo("Permohonan " + barisPilih.dataset.nama + " ditolak.");
  saring();
});

document.querySelectorAll("[data-tutup]").forEach((b) => {
  b.addEventListener("click", () => b.closest("dialog").close());
});

baris.forEach(gambar);
saring();