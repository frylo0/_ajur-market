import { VisibilityController } from './visibility.js';
import { Queue } from './queue.js';

const devicer = document.querySelector('.devicer');

class OnScrollAnimation {
   /**
    * @param {String} visibilityObserver
    * @param {String} toBeAnimated
    * @param {String} key 
    * @param {Keyframe} stateInitial 
    * @param {Keyframe} stateVisible 
    * @param {KeyframeAnimationOptions} animationOptions
    */
   constructor(visibilityObserver, toBeAnimated, key, stateInitial, stateVisible, delay, animationOptions = { duration: 500, easing: 'ease' }, mode = 'full') {
      const queue = new Queue();
      const self = this;
      self.queue = queue;

      const animationPromiseGenerator = el => Queue.delay(delay, async () => {
         const animation = el.animate([
            stateInitial,
            stateVisible,
         ], animationOptions);

         await animation.finished;
         for (const prop in stateVisible) {
            el.style[prop] = stateVisible[prop];
         }
      });

      self.visibilityControllers = [];


      $(visibilityObserver).each(function (i) {
         const id = `${key}-${i}`;

         let targetItem;
         if (toBeAnimated != visibilityObserver)
            targetItem = this.querySelector(toBeAnimated);
         else
            targetItem = this;

         const visibilityController = new VisibilityController(`${id}`, this, devicer);
         self.visibilityControllers.push(visibilityController);
         let state = { invisible: true, i };


         if (document.createElement('div').animate) { // has animate API
            when([
               `visibility controller :: become ${mode} visible (${id})`,
               `visibility controller :: startup with state full (${id})`,
               `visibility controller :: startup with state partly (${id})`,
            ], (item, dir) => onVisible(targetItem, dir, state));
            when([
               `visibility controller :: become invisible (${id})`,
               `visibility controller :: startup with state not (${id})`
            ], (item, dir) => onInvisible(targetItem, dir, state));

            visibilityController.handler();
         }

         else { // no animate API
            when([
               `visibility controller :: startup with state ${mode} (${id})`,
               `visibility controller :: startup with state not (${id})`,
            ], (item, dir) => {
               for (const prop in stateVisible) {
                  el.style[prop] = stateVisible[prop];
               }
            });
         }
      });


      async function onVisible(el, direction, state) {
         if (direction == 'up' || !state.invisible) return;

         state.invisible = false;
         queue.add(state.i, animationPromiseGenerator(el));
      }

      function onInvisible(el, direction, state) {
         if (direction == 'down') return;

         state.invisible = true;
         for (const prop in stateInitial) {
            el.style[prop] = stateInitial[prop];
         }
      }
   }
}


export {
   OnScrollAnimation,
};