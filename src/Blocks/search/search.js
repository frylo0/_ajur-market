$(document).ready(() => {
   const pref = '.search'; // prefix for current folder

   $(pref + '__input').on('keydown', e => {
      e.target.animate([
         { 'borderColor': '#FAD958' },
         { 'borderColor': '#E5E5E5' },
      ], {
         duration: 100,
         iterations: 1,
      });
   });
});