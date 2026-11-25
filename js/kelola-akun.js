const PERAN = { mahasiswa: "Mahasiswa", panitia: "Panitia", admin: "Admin" };
const STATUS = { aktif: "Aktif", nonaktif: "Nonaktif", menunggu: "Menunggu verifikasi" };

const tbody = document.getElementById("isi-tabel");
const baris = [...tbody.querySelectorAll("tr")];
const cari = document.getElementById("cari");
const fPeran = document.getElementById("f-peran");
const fStatus = document.getElementById("f-status");
const jumlah = document.getElementById("jumlah");
const kosong = document.getElementById("kosong");
const info = document.getElementById("info");

const dlgPeran = document.getElementById("dialog-peran");
const formPeran = document.getElementById("form-peran");
const teksPeran = document.getElementById("teks-peran");
const peringatan = document.getElementById("peringatan-peran");
const dlgDetail = document.getElementById("dialog-detail");
const isiDetail = document.getElementById("isi-detail");

let barisPilih = null;

function buat(tag, kelas, teks) {
  const el = document.createElement(tag);
  if (kelas) el.className = kelas;
  if (teks !== undefined) el.textContent = teks;
  return el;
}

function tombol(aksi, kelas, teks, nonaktif, petunjuk) {
  const b = buat("button", "aksi " + kelas, teks);
  b.type = "button";
  b.dataset.aksi = aksi;
  if (nonaktif) {
    b.disabled = true;
    if (petunjuk) b.title = petunjuk;
  }
  return b;
}

// isi kolom peran, status, dan aksi dari atribut data-* pada baris
function gambar(tr) {
  const d = tr.dataset;
  const sel = tr.children;

  sel[1].replaceChildren(buat("span", "peran-badge peran-" + d.peran, PERAN[d.peran]));
  sel[2].replaceChildren(buat("span", "status " + d.status, STATUS[d.status]));

  const aksi = [tombol("detail", "aksi-detail", "Detail")];

  if (d.status === "menunggu") {
    const a = buat("a", "aksi aksi-periksa", "Periksa berkas");
    a.href = "verifikasi.php";
    aksi.push(a);
  } else if (d.saya === "1") {
    aksi.push(buat("span", "teks-saya", "Akun kamu"));
  } else {
    aksi.push(tombol("peran", "aksi-detail", "Ubah peran"));
    aksi.push(tombol("status", "aksi-lain", d.status === "aktif" ? "Nonaktifkan" : "Aktifkan"));
    aksi.push(
      tombol("hapus", "aksi-hapus", "Hapus", d.punyaData === "1",
        "Punya data pendaftaran atau event. Nonaktifkan saja.")
    );
  }

  sel[4].replaceChildren(...aksi);
}

function tampilInfo(teks) {
  info.textContent = teks;
  info.hidden = false;
}

function saring() {
  const kata = cari.value.trim().toLowerCase();
  let tampil = 0;
  let total = 0;

  baris.forEach((tr) => {
    if (!tr.isConnected) return;
    total++;
    const d = tr.dataset;
    const cocok =
      (d.nama + " " + d.email + " " + d.nim).toLowerCase().includes(kata) &&
      (!fPeran.value || d.peran === fPeran.value) &&
      (!fStatus.value || d.status === fStatus.value);

    tr.hidden = !cocok;
    if (cocok) tampil++;
  });

  jumlah.textContent = "Menampilkan " + tampil + " dari " + total + " akun";
  kosong.hidden = tampil !== 0;
}

function bukaDetail(tr) {
  const d = tr.dataset;
  const data = [
    ["Nama", d.nama],
    ["Email", d.email],
    ["Peran", PERAN[d.peran]],
    ["Status", STATUS[d.status]],
  ];
  if (d.peran === "mahasiswa" && d.nim) data.push(["NIM", d.nim]);
  data.push(["Terdaftar", d.terdaftar], ["Aktivitas", d.aktivitas]);

  isiDetail.replaceChildren(
    ...data.map(([k, v]) => {
      const baris = buat("div");
      baris.append(buat("dt", "", k), buat("dd", "", v || "-"));
      return baris;
    })
  );
  dlgDetail.showModal();
}

function bukaPeran(tr) {
  barisPilih = tr;
  teksPeran.textContent = tr.dataset.nama + " (" + tr.dataset.email + ")";
  formPeran.querySelectorAll('input[name="peran-baru"]').forEach((r) => {
    r.checked = r.value === tr.dataset.peran;
  });
  peringatan.hidden = true;
  dlgPeran.showModal();
}

// klik tombol aksi di tabel
tbody.addEventListener("click", (e) => {
  const t = e.target.closest("button[data-aksi]");
  if (!t || t.disabled) return;

  const tr = t.closest("tr");
  const d = tr.dataset;

  if (t.dataset.aksi === "detail") return bukaDetail(tr);
  if (t.dataset.aksi === "peran") return bukaPeran(tr);

  if (t.dataset.aksi === "status") {
    const aktif = d.status === "aktif";
    const pesan = aktif
      ? 'Nonaktifkan akun "' + d.nama + '"? Pengguna tidak bisa login sampai diaktifkan kembali.'
      : 'Aktifkan kembali akun "' + d.nama + '"?';

    if (confirm(pesan)) {
      d.status = aktif ? "nonaktif" : "aktif";
      gambar(tr);
      tampilInfo("Akun " + d.nama + (aktif ? " dinonaktifkan." : " diaktifkan kembali."));
      saring();
    }
    return;
  }

  if (t.dataset.aksi === "hapus") {
    if (confirm('Hapus akun "' + d.nama + '"? Tindakan ini tidak bisa dibatalkan.')) {
      tr.remove();
      tampilInfo("Akun " + d.nama + " dihapus.");
      saring();
    }
  }
});

// simpan peran baru
formPeran.addEventListener("submit", (e) => {
  e.preventDefault();

  const baru = formPeran.querySelector('input[name="peran-baru"]:checked').value;
  const lama = barisPilih.dataset.peran;

  if (baru === lama) {
    peringatan.textContent = "Pilih peran yang berbeda dari peran saat ini.";
    peringatan.hidden = false;
    return;
  }

  barisPilih.dataset.peran = baru;
  gambar(barisPilih);
  dlgPeran.close();
  tampilInfo(
    "Peran " + barisPilih.dataset.nama + " diubah dari " + PERAN[lama] + " menjadi " + PERAN[baru] + "."
  );
  saring();
});

document.querySelectorAll("[data-tutup]").forEach((b) => {
  b.addEventListener("click", () => b.closest("dialog").close());
});

[cari, fPeran, fStatus].forEach((el) => {
  el.addEventListener(el === cari ? "input" : "change", saring);
});

baris.forEach(gambar);
saring();