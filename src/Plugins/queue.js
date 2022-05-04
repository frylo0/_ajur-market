class Queue {
   constructor() {
      this.queue = [];
      this.state = 'paused';
   }

   add(key, promiseGenerator) {
      for (let i = 0, anim; anim = this.queue[i]; i++)
         if (anim[0] == key) return;

      this.queue.push([key, promiseGenerator]);
      this.startPlayNext();
   }

   async startPlayNext() {
      if (this.state != 'paused') return;

      this.state = 'playing';
      await this.playNext();
   }

   async playNext() {
      let [, promiseGenerator] = this.queue.shift();
      await promiseGenerator(); // run and await promise
      if (this.queue.length > 0) {
         this.playNext();
      } else {
         this.state = 'paused';
      }
   }

   static delay(time, callback) {
      return () => new Promise(async res => {
         setTimeout(res, time);

         callback();
      });
   }
}

export {
   Queue,
};