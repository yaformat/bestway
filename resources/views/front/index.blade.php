<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Фронтенд</title>
</head>
<body>
    <div id="app">
        <h1>Фронтенд работает!</h1>
        <p>Текущий домен: {{ request()->getHost() }}</p>
        <p>URL: {{ url()->current() }}</p>
        <a href="/admin">Перейти в админку</a>
    </div>
</body>
</html>