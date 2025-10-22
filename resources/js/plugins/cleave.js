// src/resources/js/plugins/cleave.js
import Cleave from 'cleave.js';

export default {
  mounted(el, binding) {
    // Find the input element within the Vuetify component
    const input = el.querySelector('input');
    if (input) {
      try {
        new Cleave(input, binding.value);
      } catch (error) {
        console.error('Error initializing Cleave.js:', error);
      }
    } else {
      console.error('Cleave.js: Input element not found');
    }
  }
};
