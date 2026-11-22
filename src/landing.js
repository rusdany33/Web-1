'use strict';

// [LAND-01] Elemen halaman Modul 1.
const menuButton = document.querySelector('#menu-toggle');
const menu = document.querySelector('#menu-nav');
const contactForm = document.querySelector('#contact-form');
const formStatus = document.querySelector('#form-status');

// [LAND-02] KLIK MENU -> ubah status aria dan class -> menu tampil/tersembunyi.
function toggleMenu() {
  const isOpen = menuButton.getAttribute('aria-expanded') !== 'true';
  menuButton.setAttribute('aria-expanded', String(isOpen));
  menu.classList.toggle('menu-open', isOpen);
}

// [LAND-03] SUBMIT -> validasi field -> pesan gagal atau konfirmasi demo.
function handleContactSubmit(event) {
  event.preventDefault();
  const nameInput = contactForm.elements.nama;
  const messageInput = contactForm.elements.pesan;
  nameInput.setCustomValidity(nameInput.value.trim() ? '' : 'Nama wajib diisi.');
  messageInput.setCustomValidity(messageInput.value.trim() ? '' : 'Pesan wajib diisi.');

  if (!contactForm.reportValidity()) {
    formStatus.textContent = 'Periksa kembali nama, format email, dan pesan kamu.';
    return;
  }

  formStatus.textContent = `Terima kasih, ${nameInput.value.trim()}! Form valid. Ini demo validasi; pesan tidak dikirim ke server.`;
}

// [LAND-04] INPUT FORM -> hapus validasi lama agar pengguna bisa memperbaiki isian.
function handleContactInput(event) {
  event.target.setCustomValidity?.('');
  formStatus.textContent = '';
}

// [LAND-05] TITIK MULAI LANDING: hubungkan event dengan fungsi bernama.
menuButton.addEventListener('click', toggleMenu);
contactForm.addEventListener('submit', handleContactSubmit);
contactForm.addEventListener('input', handleContactInput);
