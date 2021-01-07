import { OnScrollAnimation } from './../../Plugins/on-scoll-animation.js';

$(document).ready(() => {
   const pref = '.card-product'; // prefix for current folder

   new OnScrollAnimation(`${pref}`, `${pref}`, 'card-product',
      { transform: 'translateY(20px) scale(0.95)', opacity: '0.5' },
      { transform: 'translateY(0px) scale(1)', opacity: '1' },
      100, { duration: 500, easing: 'ease' }, 'partly');
});