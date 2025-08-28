<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laravel Push Notification</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <h1>Laravel + Firebase Push</h1>

    <button onclick="initFirebaseMessagingRegistration()">Allow Notifications</button>

    <div id="fcm-token"></div>

    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        var firebaseConfig = {
            apiKey: "",
            authDomain: "",
            databaseURL: "",
            projectId: "",
            storageBucket: "",
            messagingSenderId: "",
            appId: ""
        };
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        async function initFirebaseMessagingRegistration() {
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    const token = await messaging.getToken({
                        vapidKey: "" // Add your VAPID key here if needed
                    });

                    console.log("FCM Token:", token);
                    $('#fcm-token').html('<p>Your FCM Token: ' + token + '</p>');

                    // Save token to backend
                    $.ajax({
                        url: '{{ route('save-token') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            token: token
                        },
                        success: function(res) {
                            console.log(res.status);
                        },
                        error: function(err) {
                            console.error('Error saving token:', err);
                        }
                    });
                } else {
                    console.warn("Notification permission not granted.");
                }
            } catch (err) {
                console.error("FCM Error:", err);
            }
        }

        // Listen for messages
        messaging.onMessage((payload) => {
            console.log("Message received:", payload);
            alert(payload.notification.title + " - " + payload.notification.body);
        });
    </script>
</body>

</html>
