const kartu = document.querySelectorAll(".event");
const cari = document.getElementById("cari");
const status = document.getElementById("status");
const kosong = document.getElementById("kosong");

function saring() {
  const kata = cari.value.trim().toLowerCase();
  let tampil = 0;

  kartu.forEach((k) => {
    const cocokKata = k.textContent.toLowerCase().includes(kata);
    const cocokStatus = status.value === "semua" || k.dataset.status === status.value;
    const lihat = cocokKata && cocokStatus;

    k.hidden = !lihat;
    if (lihat) tampil++;
  });

  kosong.hidden = tampil !== 0;
}

cari.addEventListener("input", saring);
status.addEventListener("change", saring);
