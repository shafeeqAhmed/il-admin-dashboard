<?php

namespace App\Helpers;

use App\ScreenSetting;
use Illuminate\Support\Facades\Validator;
use DB;

Class HomeScreenHelper {
    /*
      |--------------------------------------------------------------------------
      | HomeScreenHelper that contains all the home screen related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use home screen processes
      |
     */

    /**
     * Description of HomeScreenHelper
     *
     * @author ILSA Interactive
     */
    public static function customizeHomeScreen($inputs) {
        $validation = Validator::make($inputs, HomeScreenValidationHelper::customizeHomeScreenRules()['rules'], HomeScreenValidationHelper::customizeHomeScreenRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }

        $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid($inputs['profile_uuid']);

        $data_inputs = ['freelancer_id' => $inputs['freelancer_id'], 'show_option' => $inputs['show_option']];



        $result = ScreenSetting::createOrUpdate('freelancer_id', $inputs['freelancer_id'], $data_inputs);
        if ($result) {
            DB::commit();
            return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request']);
        }
        DB::rollBack();
        return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['success_error']);
    }

}
