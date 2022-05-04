import { OnScrollAnimation } from './../../Plugins/on-scoll-animation.js';

$(document).ready(() => {
   const pref = '.footer'; // prefix for current folder

   const onScrollAnim = new OnScrollAnimation(`${pref}`, `${pref}__content`, 'footer',
      {},
      {},
      0, { duration: 500, easing: 'ease' }, 'partly');

   const visibilityController = onScrollAnim.visibilityControllers[0];
   visibilityController.when('become visible', footer => {
      const logo = footer.querySelector('.logo');
      logo.classList.add('logo_vrum');
   }).when('become invisible', footer => {
      const logo = footer.querySelector('.logo');
      logo.classList.remove('logo_vrum');
   });
});