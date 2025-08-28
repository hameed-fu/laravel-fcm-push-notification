<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;

class PushController extends Controller
{

    

    protected $auth, $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->auth = Firebase::auth();
        $this->messaging = $messaging;
    }

    // Save FCM token
    public function store_fcm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        User::where('id', auth()->id())->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json(['message' => 'FCM token stored successfully'], 200);
    }

    // Test sending notification
    public function sendNotification()
    {
        // Get user FCM token from database
        $user = auth()->user();

        if (!$user || !$user->fcm_token) {
            return response()->json([
                'status' => 'error',
                'message' => 'No FCM token found for this user',
            ], 400);
        }

        $notification = [
            "title" => "New Message",
            "body"  => "You have received a new message.",
            "image" => url('/new_logo.png'),   // actual logo
            "icon"  => url('/icon.png'),       // actual icon
            "badge" => url('/logo.png'),      // actual badge
        ];

        $data = [
            "id"  => 105,
            "url" => url('/'),
        ];

        $fcm_tokens = [$user->fcm_token];

        $resp = $this->send($fcm_tokens, $notification, $data);

        return response()->json([
            'status'   => 'sent',
            'response' => $resp,
            'tokens'   => $fcm_tokens,
        ]);
    }

    // Send notification
    public function send(array $fcm_tokens, array $notification, array $data)
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::fromArray($notification))
            ->withData($data)
            ->withAndroidConfig(AndroidConfig::fromArray([
                'notification' => [
                    'icon'   => $notification['icon'] ?? url('/icon.png'),
                    'color'  => '#f45342',
                    'sound'  => 'default',
                ],
            ]))
            ->withApnsConfig(ApnsConfig::fromArray([
                'payload' => [
                    'aps' => [
                        'badge' => 1,
                        'sound' => 'default',
                    ],
                ],
            ]))
            ->withWebPushConfig(WebPushConfig::fromArray([
                'notification' => [
                    'title' => $notification['title'] ?? 'New Message',
                    'body'  => $notification['body'] ?? '',
                    'icon'  => $notification['icon'] ?? url('/icon.png'),
                    'badge' => $notification['badge'] ?? url('/logo.png'),
                    'image' => $notification['image'] ?? null,
                ],
                'fcm_options' => [
                    'link' => $data['url'] ?? '/',
                ],
            ]));
        return $this->messaging->sendMulticast($message, $fcm_tokens);
    }
}
