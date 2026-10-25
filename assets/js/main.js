// assets/js/main.js - CampusConnect shared JavaScript

document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');

  // 1. Top menu: mobile open/close
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      const isOpen = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen);
      toggle.innerHTML = isOpen
        ? '<i class="fa-solid fa-xmark"></i>'
        : '<i class="fa-solid fa-bars"></i>';
    });

    links.querySelectorAll('a').forEach(a =>
      a.addEventListener('click', () => {
        links.classList.remove('open');
        toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
      })
    );
  }

  // 2. Sidebar menu: open/close on small screens
  const sideWrap   = document.querySelector('.side-wrap');
  const sideToggle = document.getElementById('sideToggle');
  if (sideWrap && sideToggle) {
    sideToggle.addEventListener('click', () => {
      const isOpen = sideWrap.classList.toggle('open');
      sideToggle.setAttribute('aria-expanded', isOpen);
    });
  }

  // 3. Shadow on the header when scrolling
  window.addEventListener('scroll', () => {
    if (header) header.classList.toggle('scrolled', window.scrollY > 10);
  });

  // 4. Auto-hide alerts after 5 seconds (error boxes marked alert-persist stay)
  document.querySelectorAll('.alert:not(.alert-persist)').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });
});