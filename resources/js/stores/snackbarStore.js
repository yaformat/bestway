// src/stores/snackbarStore.js
import { defineStore } from 'pinia';

export const useSnackbarStore = defineStore('snackbar', {
  state: () => ({
    message: '',
    type: '',
    visible: false,
    timeout: 3000, // Default timeout duration in milliseconds
    countdown: 0,
  }),
  actions: {
    showSnackbar(message, type, timeout = 3000) {
      this.message = message;
      this.type = type;
      this.visible = true;
      this.timeout = timeout;
      this.countdown = Math.ceil(timeout / 1000);

      if (type === 'success') {
        const countdownInterval = setInterval(() => {
          if (this.countdown > 1) {
            this.countdown -= 1;
          } else {
            clearInterval(countdownInterval);
            this.hideSnackbar();
          }
        }, 1000);
      } else {
        // Automatically hide the snackbar after the timeout duration for other types
        setTimeout(() => {
          this.hideSnackbar();
        }, this.timeout);
      }
    },
    hideSnackbar() {
      this.visible = false;
      this.countdown = 0;
    },
  },
});
