import { VisibilityController } from './../../Plugins/visibility.js';

$(document).ready(() => {
   const pref = '.header'; // prefix for current folder
   const devicer = document.querySelector('.devicer');
   const logo = document.querySelector(`${pref} .logo`);

   $(`${pref}`).each(function (i) {
      const header = this;
      const id = `${pref}-${i}`;
      const visibilityController = new VisibilityController(id, header, devicer);
      visibilityController
         .when([
            'become full visible',
            'startup',
         ], el => {
            console.log('hello');
            logo.classList.add('logo_vrum');
         })
         .when([
            'become invisible',
         ], el => logo.classList.remove('logo_vrum'))
         ;
      visibilityController.handler();

      const $menuButton = $(`${pref}__burger-button`);
      $menuButton.on('click', e => menu.open());
   });
});