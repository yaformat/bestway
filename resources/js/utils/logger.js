// src/utils/logger.js

const isDevelopment = import.meta.env.VITE_APP_ENV === 'development';

export function log(message, ...optionalParams) {
  if (isDevelopment) {
    console.log(message, ...optionalParams);
  }
}
