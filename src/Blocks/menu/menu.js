$(document).ready(() => {
   const pref = '.menu'; // prefix for current folder
   const mis = Array.from(document.querySelectorAll('.menu__item'));
   const menuEl = document.querySelector('.menu');
   const misHide = () => mis.forEach(mi => {
      mi.style.transform = 'translateY(50px)';
      mi.style.opacity = '0';
   });

   const menu = {
      open() {
         menuEl.classList.remove('dn');
         document.body.getBoundingClientRect();
         menuEl.classList.remove('menu_closed');
         menuEl.classList.add('menu_opened');
         setTimeout(() => animate(mis, 0), 0);
      },
      close() {
         menuEl.classList.remove('menu_opened');
         menuEl.classList.add('menu_closed');
         const handler = e => {
            menuEl.classList.add('dn');
            misHide();
            menuEl.removeEventListener('transitionend', handler);
         };
         menuEl.addEventListener('transitionend', handler);
      }
   };
   window.menu = menu;

   menuEl.querySelector('.menu__close-button').addEventListener('click', menu.close);
   misHide();

   async function animate(mis, i) {
      if (i == mis.length) return;

      setTimeout(() => animate(mis, i + 1), 100);

      let animation = mis[i].animate([
         { transform: 'translateY(50px)', opacity: '0' },
         { transform: 'translateY(0px)', opacity: '1' }
      ], { duration: 1000, easing: 'ease' });

      await animation.finished;

      mis[i].style.transform = 'translateY(0px)';
      mis[i].style.opacity = '1';
   }
});