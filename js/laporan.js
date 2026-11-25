const pilihEvent = document.getElementById("f-event");

// satu sel CSV: aman dari rumus berbahaya dan nomor panjang tetap jadi teks
function sel(nilai) {
  let t = String(nilai ?? "");

  if (/^\d{6,}$/.test(t)) {
    return '="' + t + '"'; // NIM tidak diubah Excel jadi angka
  }
  if (/^[=+@\t\r]/.test(t) || (t.startsWith("-") && t !== "-" && isNaN(Number(t)))) {
    t = "'" + t; // cegah isi sel dibaca sebagai rumus
  }
  return '"' + t.replace(/"/g, '""') + '"';
}

function bersih(el) {
  return el.textContent.replace(/\s+/g, " ").trim();
}

// ubah satu sel tabel menjadi satu atau dua kolom
function kolomDariSel(c, kepala) {
  if (kepala) {
    return bersih(c) === "Mahasiswa" ? ["Nama", "NIM"] : [bersih(c)];
  }
  const utama = c.querySelector(".utama");
  if (utama) {
    const sub = c.querySelector(".sub");
    return [bersih(utama), sub ? bersih(sub).replace(/^NIM\s*/i, "") : ""];
  }
  return [bersih(c)];
}

function buatCsv() {
  const namaEvent = pilihEvent.options[pilihEvent.selectedIndex].textContent.trim();
  const baris = [];

  baris.push(["Laporan pendaftaran"]);
  baris.push(["Event", namaEvent]);
  baris.push(["Dibuat", new Date().toLocaleString("id-ID")]);
  baris.push([]);

  baris.push(["Ringkasan"]);
  document.querySelectorAll(".ringkasan .ringkas-item").forEach((item) => {
    baris.push([bersih(item.querySelector("span")), bersih(item.querySelector("strong"))]);
  });
  baris.push([]);

  document.querySelectorAll("table[data-laporan]").forEach((tabel) => {
    baris.push([tabel.dataset.laporan]);
    tabel.querySelectorAll("tr").forEach((tr) => {
      const kepala = tr.parentElement.tagName === "THEAD";
      baris.push([...tr.children].flatMap((c) => kolomDariSel(c, kepala)));
    });
    baris.push([]);
  });

  // "sep=;" memberi tahu Excel pemisah kolomnya, agar rapi di pengaturan bahasa apa pun
  return "sep=;\r\n" + baris.map((b) => b.map(sel).join(";")).join("\r\n");
}

function unduhCsv() {
  const namaEvent = pilihEvent.options[pilihEvent.selectedIndex].textContent.trim();
  const slug = namaEvent.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "");
  const tanggal = new Date().toISOString().slice(0, 10);

  const blob = new Blob(["\uFEFF" + buatCsv()], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = "laporan-" + slug + "-" + tanggal + ".csv";
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

document.getElementById("unduh-csv").addEventListener("click", unduhCsv);
document.getElementById("cetak").addEventListener("click", () => window.print());