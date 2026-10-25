// assets/js/main.js - CampusConnect shared JavaScript

document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');

  // 1. Mobile menu open/close
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      const isOpen = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen);
      toggle.innerHTML = isOpen
        ? '<i class="fa-solid fa-xmark"></i>'
        : '<i class="fa-solid fa-bars"></i>';
    });

    // Close the menu when a link is tapped
    links.querySelectorAll('a').forEach(a =>
      a.addEventListener('click', () => {
        links.classList.remove('open');
        toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
      })
    );
  }

  // 2. Add shadow to the header when scrolling
  window.addEventListener('scroll', () => {
    if (header) header.classList.toggle('scrolled', window.scrollY > 10);
  });

  // 3. Auto-hide alert messages after 5 seconds
 document.querySelectorAll('.alert:not(.alert-persist)').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });
});