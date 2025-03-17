importScripts('https://www.gstatic.com/firebasejs/11.4.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.4.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyCgbgKUHetzCsLfrGqNpIsSrCiMaFAKYwo",
    authDomain: "ukourhome.firebaseapp.com",
    projectId: "ukourhome",
    storageBucket: "ukourhome.firebasestorage.app",
    messagingSenderId: "467716538818",
    appId: "1:467716538818:web:8c2f32fdd3c5eba69d165b",
    measurementId: "G-4BCXWHBR61"
});

const messaging = firebase.messaging();

// Обработка фоновых сообщений
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Получено фоновое сообщение ', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/path/to/icon.png'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
