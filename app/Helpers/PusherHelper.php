<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;

class PusherHelper{
     /*
       |--------------------------------------------------------------------------
       | PusherHelper that contains all the Android Pusher methods for APIs
       |--------------------------------------------------------------------------
       |
       | This Helper controls all the methods that use Android Pusher processes
       |
      */

    public static function getBeamsClient(){
        return new PushNotifications(array(
            "instanceId" => env('PUSHER_PUSH_INSTANCE_ID'),
            "secretKey" => env('PUSHER_PUSH_SECRET_KEY'),
        ));
    }

    public static function sendAndroidNotification($userUUID, $data = null){
        $beamsClient = PusherHelper::getBeamsClient();

        Log::info('Pusher Android Sending Notification ', [
            'user_uuid' => $userUUID,
            'data' => $data,
        ]);
        try {
            $publishResponse = $beamsClient->publishToUsers(
//                array("a3d183bf-8463-4889-b8e6-38ff53ca50eb"),
                            array($userUUID),
                array(
                    "fcm" => array(
//                        "notification" => array(
//                            "title" => $data['message'] ?? 'You have a notification',
//                            "body" => ''
//                        ),
                        "data" => [
                            'data' => $data
                        ]
                    ),
                ));
        } catch (\Exception $e) {
            Log::info('Pusher Android Notification Error: ', [
                'exception' => $e,
                'user_uuid' => $userUUID,
                'data' => $data,
            ]);
        }
    }
}
