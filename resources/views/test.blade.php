<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документация API</title>
</head>
<body>
<script type="module">
    // Импорт базовых функций Firebase
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.4.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.4.0/firebase-analytics.js";
    // Импорт модуля Cloud Messaging
    import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.4.0/firebase-messaging.js";

    // Конфигурация Firebase
    const firebaseConfig = {
        apiKey: "AIzaSyCgbgKUHetzCsLfrGqNpIsSrCiMaFAKYwo",
        authDomain: "ukourhome.firebaseapp.com",
        projectId: "ukourhome",
        storageBucket: "ukourhome.firebasestorage.app",
        messagingSenderId: "467716538818",
        appId: "1:467716538818:web:8c2f32fdd3c5eba69d165b",
        measurementId: "G-4BCXWHBR61"
    };

    // Инициализация Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);

    // Инициализация Firebase Cloud Messaging
    const messaging = getMessaging(app);

    // Запрос разрешения на отправку уведомлений
    Notification.requestPermission().then((permission) => {
        if (permission === 'granted') {
            console.log('Разрешение на уведомления получено');

            // Получение FCM токена
            getToken(messaging, { vapidKey: 'BCfEStXrH0GZr8NKdF_UPURHxIJ7FERg9LYJTlg9O3SrWrhRKg75TXC_c9XGl80dg5CO_UoQT_ItUziToxs0IJc' }).then((currentToken) => {
                if (currentToken) {
                    // Токен получен, можно отправлять на сервер
                    console.log('FCM токен устройства:', currentToken);

                    // Здесь вы можете отправить токен на свой сервер
                    sendTokenToServer(currentToken);
                } else {
                    console.log('Не удалось получить токен.');
                }
            }).catch((err) => {
                console.log('Ошибка при получении токена:', err);
            });
        } else {
            console.log('Разрешение на уведомления не получено');
        }
    });

    // Обработчик входящих сообщений (когда приложение открыто)
    onMessage(messaging, (payload) => {
        console.log('Получено сообщение:', payload);

        // Показать уведомление
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/path/to/icon.png'
        };

        new Notification(notificationTitle, notificationOptions);§
    });

    // Функция для отправки токена на сервер
    function sendTokenToServer(token) {
        // В реальном приложении здесь будет POST запрос на ваш сервер
        console.log('Токен отправлен на сервер:', token);
    }
</script>
</body>
</html>
