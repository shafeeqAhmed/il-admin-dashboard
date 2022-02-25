<?php

namespace App\Helpers;

use App\Freelancer;
use App\User;
use Illuminate\Support\Facades\Validator;
use DB;

class UserHelper {

    public static function makeUserStoreResponse($input = []) {
        $response = [];
        if (!empty($input)) {
            $response['user_uuid'] = $input['user_uuid'];
            $response['profile_type'] = $input['verification_type'] == 'freelancer_signup' ? 'freelancer' : 'customer';
            $response['first_name'] = $input['first_name'];
            $response['last_name'] = $input['last_name'];
            $response['email'] = $input['email'];
            $response['phone_number'] = $input['phone_number'];
            $response['country_code'] = $input['country_code'];
            $response['country_name'] = $input['country_name'];
            $response['password'] = $input['password'];
            $response['facebook_id'] = null;
            $response['google_id'] = null;
            $response['apple_id'] = null;
            $response['onboard_count'] = null;
            $response['default_currency'] = $input['default_currency'];
        }
        return $response;
    }

    public static function updateUser($inputs = []) {

        $validation = Validator::make($inputs, UserValidationHelper::updateProfileRules()['rules'], UserValidationHelper::updateProfileRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }

        $check_user = User::checkUser('user_uuid', $inputs['user_uuid']);
        if (!$check_user) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(UserMessageHelper::getMessageData('error', $inputs['lang'])['invalid_uuid']);
        }


        // Here we are simple updating the basic info input of user table
        $user_inputs_data = UserValidationHelper::processUserInputs($inputs, $check_user);
        if (!$user_inputs_data['success']) {
            return CommonHelper::jsonErrorResponse($user_inputs_data['message']);
        }

        $user_inputs = $user_inputs_data['data'];

        if (!empty($inputs['phone_number'])) {
            $phone_number = !empty($check_user['phone_number']) ? $check_user['phone_number'] : null;
            $update_number = $inputs['phone_number'];
            if (!empty($phone_number) && $inputs['phone_number'] == $phone_number) {
                $update_number = null;
            }
            if (!empty($update_number)) {
                $validation = Validator::make($inputs, UserValidationHelper::uniqueUserPhoneRules()['rules'], UserValidationHelper::uniqueUserPhoneRules()['message_' . strtolower($inputs['lang'])]);
                if ($validation->fails()) {
                    return CommonHelper::jsonErrorResponse($validation->errors()->first());
                }
            }
            $user_inputs['phone_number'] = !empty($update_number) ? $update_number : $inputs['phone_number'];
            $user_inputs['country_code'] = !empty($inputs['country_code']) ? $inputs['country_code'] : null;
            $user_inputs['country_name'] = !empty($inputs['country_name']) ? $inputs['country_name'] : null;
        }

        $update = User::updateUser('user_uuid', $inputs['user_uuid'], $user_inputs);

        if (!$update) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(UserMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
        }
        $user = user::getUserDetail('user_uuid', $inputs['user_uuid']);

        $response = UserResponseHelper::prepareSignupResponse($user);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['update_success'], $response);
    }

}
