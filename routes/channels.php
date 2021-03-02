<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat', function ($user) {
    return $user;
});

Broadcast::channel('payment', function ($file) {
    return $file;
});

Broadcast::channel('payment_commission', function ($file) {
    return $file;
});

Broadcast::channel('payment_account', function ($file) {
    return $file;
});

// Broadcast::channel('posts', function ($post) {
//     return $post;
// });
Broadcast::channel('appointmentCreated', function ($appointment) {
    return $appointment->id;
});

Broadcast::channel('Activity-Monitor', function ($data) {
    return $data;
});

Broadcast::channel('create-studio', function ($file) {
    return $file;
});
