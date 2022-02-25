<?php


namespace App\Http\Controllers;


use App\Helpers\CommonHelper;
use App\Helpers\ExceptionHelper;
use App\Helpers\PusherHelper;
use App\UserDevice;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;

class PusherAndroidController extends Controller{

    public function tokenProvider(Request $request){
        try {
            DB::beginTransaction();
            Log::info('Beams Auth Log:', $request->all());
            $loggedUserUUID = $request->input('logged_in_uuid');
            $beamsClient = PusherHelper::getBeamsClient();
            $beamsToken = $beamsClient->generateToken($loggedUserUUID);

            $token = $beamsToken['token'];

            $userDevice = UserDevice::where('profile_uuid', '=', $loggedUserUUID)
                ->where('device_type', '=', 'android')
                ->where('is_archive', '=', 0)->first();

            if (empty($userDevice)):
                UserDevice::createDevice([
                    'profile_uuid' => $loggedUserUUID,
                    'device_type' => 'android',
                    'device_token' => $token,
                    'is_archive' => 0,
                ]);
            else:
                $userDevice->device_token = $token;
                $userDevice->save();
            endif;
            DB::commit();
            return response()->json(array_merge([
                'success' => true
            ], $beamsToken));
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            Log::info('Beams Query Auth Error:', [
                'exception' => $ex,
                'inputs' => $request->all()
            ]);
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }catch (\Exception $ex) {
            DB::rollBack();
            Log::info('Beams Auth Error:', [
                'exception' => $ex,
                'inputs' => $request->all()
            ]);
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function sendTestNotification(Request $request){

//        try {
        Log::info('Beams Send Notification Log:', $request->all());
        $beamsClient = PusherHelper::getBeamsClient();

        $publishResponse = $beamsClient->publishToUsers(
//            array("a3d183bf-8463-4889-b8e6-38ff53ca50eb"),
            array($request->input('logged_in_uuid')),
            array(
                "fcm" => array(
                    "notification" => array(
                        "title" => "Hi!",
                        "body" => "This is my first Push Notification!"
                    ),
                    "data" => [
                        'data' => [
                            'test' => 1,
                            'string' => 'H'
                        ]
                    ]
                ),
            ));
        return CommonHelper::jsonSuccessResponse('Success',[
            'res' => $publishResponse
        ]);
//        } catch (\Exception $e) {
//
//        }

    }
}
