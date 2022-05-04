//const { action } = require('./eventone.js');

class VisibilityController {
   constructor(i, element, parent = window) {
      let self = this;

      self.element = element;
      self.parent = parent;

      self.prevState = undefined;
      self.state = 'not';

      self.prevScrollTop = 0;
      self.scrollDir = undefined;

      self.i = i;

      function handler(e) {
         self.scrollDir = parent.scrollTop >= self.prevScrollTop ? 'down' : 'up';
         self.prevScrollTop = parent.scrollTop;

         const rect = element.getBoundingClientRect();
         const parentHeight = parent.clientHeight;

         const isStartup = self.prevState === undefined;

         self.prevState = self.state;
         if (rect.bottom < parentHeight && rect.top > 0)
            self.state = 'full';
         if (rect.top > parentHeight || rect.bottom < 0)
            self.state = 'not';
         if (rect.top < parentHeight && rect.bottom > parentHeight ||
            rect.bottom > 0 && rect.top < 0)
            self.state = 'partly';

         if (isStartup) {
            if (parent.scrollTop == 0 && self.state == 'not') self.scrollDir = 'up';
            actionLoc(`startup with state ${self.state}`);
            actionLoc('startup');
            return;
         }

         if (self.state == 'full') {
            if (self.prevState != 'full') actionLoc('become full visible');
            actionLoc('is full visible');
            actionLoc('is visible');
         }
         else if (self.state == 'partly') {
            if (self.prevState != 'partly') {
               if (self.prevState == 'full') actionLoc('full to partly');
               else if (self.prevState == 'not') {
                  actionLoc('not to partly');
                  actionLoc('become visible');
               }
               actionLoc('become partly visible');
            }
            actionLoc('is partly visible');
            actionLoc('is visible');
         }
         else if (self.state == 'not') {
            if (self.prevState != 'not') actionLoc('become invisible');
            actionLoc('is invisible');
         }
         else {
            console.error('Visibility Controller: unknown state = ' + self.state);
         }

         function actionLoc(name) {
            action(`visibility controller :: ${name} (${i})`)(self.element, self.scrollDir, self.parent, e);
         }
      }

      parent.addEventListener('scroll', handler);
      this.handler = () => handler();
   }

   when(action, callback) {
      if (Array.isArray(action)) {
         for (const singleAction of action) {
            when(`visibility controller :: ${singleAction} (${this.i})`, callback);
         }
      } else {
         const singleAction = action;
         when(`visibility controller :: ${singleAction} (${this.i})`, callback);
      }

      return this;
   }
}

export {
   VisibilityController
};;