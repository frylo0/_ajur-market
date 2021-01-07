import { VisibilityController } from './../../Plugins/visibility';

$(document).ready(() => {
   const pref = '.how-work-block'; // prefix for current folder
   const devicer = document.querySelector('.devicer');
   const power = -0.2;

   $(`${pref}__image`).each(function (i) {
      const id = `${pref}__image-${i}`;
      const visibilityController = new VisibilityController(id, this, devicer);

      visibilityController.when('is visible', (el, direction, parent) => {
         const elRect = el.getBoundingClientRect();
         const elMiddle = (elRect.top + elRect.bottom) / 2;
         const parMiddle = parent.offsetHeight / 2;
         let pos = elMiddle - parMiddle;
         pos *= power;
         const pxVal = Math.abs(pos);
         el.style.backgroundPositionY = `calc(50% ${pos < 0 ? '-' : '+'} ${pxVal}px)`;
      });
   });
});