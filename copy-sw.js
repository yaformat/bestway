const fs = require('fs');
const path = require('path');

// Функция для копирования файла
function copyFile(source, destination) {
  try {
    if (fs.existsSync(source)) {
      fs.copyFileSync(source, destination);
      console.log(`Файл скопирован из ${source} в ${destination}`);
    } else {
      console.error(`Файл не найден: ${source}`);
    }
  } catch (error) {
    console.error(`Ошибка при копировании файла: ${error.message}`);
  }
}

// Копируем service-worker.js из build в корень public
copyFile(
  path.join(__dirname, 'public/build', 'service-worker.js'),
  path.join(__dirname, 'public', 'service-worker.js')
);

// Копируем workbox файлы
const buildDir = path.join(__dirname, 'public/build');
const publicDir = path.join(__dirname, 'public');

// Читаем все файлы в директории build
fs.readdirSync(buildDir).forEach(file => {
  if (file.startsWith('workbox-')) {
    copyFile(
      path.join(buildDir, file),
      path.join(publicDir, file)
    );
  }
});

// Копируем manifest.webmanifest или manifest.json, если они существуют
copyFile(
  path.join(buildDir, 'manifest.webmanifest'),
  path.join(publicDir, 'manifest.webmanifest')
);

copyFile(
  path.join(buildDir, 'manifest.json'),
  path.join(publicDir, 'manifest.json')
);

// Создаем простой registerSW.js в корне public
const registerSWContent = `
if('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
      .then(registration => {
        console.log('SW registered: ', registration);
      })
      .catch(error => {
        console.error('SW registration failed: ', error);
      });
  });
}
`;

fs.writeFileSync(
  path.join(publicDir, 'registerSW.js'),
  registerSWContent
);
console.log('Создан файл registerSW.js в корне public');


// Модифицируем service-worker.js
const serviceWorkerPath = path.join(__dirname, 'public', 'service-worker.js');
if (fs.existsSync(serviceWorkerPath)) {
  let serviceWorkerContent = fs.readFileSync(serviceWorkerPath, 'utf8');
  
  // Удаляем регистрацию NavigationRoute для index.html
  serviceWorkerContent = serviceWorkerContent.replace(
    /registerRoute\(new NavigationRoute\(createHandlerBoundToURL\(['"]index\.html['"]\)\)\);?/g,
    '// Navigation route disabled for Laravel'
  );
  
  // Удаляем любые другие ссылки на index.html
  serviceWorkerContent = serviceWorkerContent.replace(
    /createHandlerBoundToURL\(['"]index\.html['"]\)/g,
    'null /* index.html not available in Laravel */'
  );
  
  fs.writeFileSync(serviceWorkerPath, serviceWorkerContent);
  console.log('service-worker.js модифицирован для работы с Laravel');
}
