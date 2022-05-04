import { OnScrollAnimation } from './../../Plugins/on-scoll-animation.js';

$(document).ready(() => {
   const pref = '.card-shop'; // prefix for current folder

   new OnScrollAnimation(`${pref}`, `${pref}`, 'card-shop',
      { transform: 'scale(0.9)', opacity: '0' },
      { transform: 'scale(1)', opacity: '1' },
      150, { duration: 300, easing: 'ease' }, 'partly');
});