<?php

namespace App\Helpers;

use App\User;
use Illuminate\Support\Facades\Validator;
use App\Freelancer;

Class UserValidationHelper {
    /*
      |--------------------------------------------------------------------------
      | FreelancerValidationHelper that contains all the Freelancer Validation methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use Freelancer processes
      |
     */

    public static function updateProfileRules() {
        $validate['rules'] = [
            'user_uuid' => 'required',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function uniqueUserEmailRules($id = 1) {
        $validate['rules'] = [
            'email' => 'required|unique:users,email,' . $id,
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function uniqueUserPhoneRules() {
        $validate['rules'] = [
            'phone_number' => 'unique:users',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function englishMessages() {
        return [
            'user_uuid.required' => 'User Uuid is missing',
            'first_name.required' => 'First name is missing',
            'last_name.required' => 'Last name is missing',
            'company.required' => 'Company name is missing',
            'email.required' => 'Email is missing',
            'email.unique' => 'This email has already been taken.',
            'phone_number.required' => 'Phone number is missing',
            'phone_number.unique' => 'Phone number already taken',
            'country_code.required' => 'Phone country code is missing',
            'country_name.required' => 'Phone country name is missing',
            'password.required' => 'Password is missing',
        ];
    }

    public static function arabicMessages() {
        return [
            'user_uuid.required' => 'معرف المستخدم مفقود',
            'first_name.required' => 'الاسم مفقود',
            'last_name.required' => 'الاسم مفقود',
            'email.required' => 'البريد الإلكتروني مفقود',
            'email.unique' => 'وقد تم بالفعل اتخاذ هذا البريد الإلكتروني',
            'phone_number.required' => 'رقم الهاتف مفقود',
            'country_code.required' => 'Phone country code is missing',
            'country_name.required' => 'Phone country name is missing',
            'password.required' => 'كلمة المرور مفقودة',
        ];
    }

    public static function processUserInputs($inputs = [], $check_user = []) {
        $user_inputs = ['success' => true, 'message' => 'Successful request', 'data' => []];

        if (!empty($inputs['first_name'])) {
            $user_inputs['data']['first_name'] = $inputs['first_name'];
        }
        if (!empty($inputs['last_name'])) {
            $user_inputs['data']['last_name'] = $inputs['last_name'];
        }
        if (!empty($inputs['country_code'])) {
            $user_inputs['data']['country_code'] = $inputs['country_code'];
        }
        if (!empty($inputs['country_name'])) {
            $user_inputs['data']['country_name'] = $inputs['country_name'];
        }
        if (!empty($inputs['phone_number'])) {
            $user_inputs['data']['phone_number'] = $inputs['phone_number'];
        }
        if (!empty($inputs['profile_image'])) {
            MediaUploadHelper::moveSingleS3Image($inputs['profile_image'], CommonHelper::$s3_image_paths['freelancer_profile_image']);
            $result = ThumbnailHelper::processThumbnails($inputs['profile_image'], 'profile_image', 'freelancer');
            if (!$result['success']) {
                return CommonHelper::jsonErrorResponse("Profile image could not be processed");
            }
            $user_inputs['data']['profile_image'] = $inputs['profile_image'];
        }

        if (!empty($inputs['email'])) {
            $email = !empty($check_user['email']) ? $check_user['email'] : null;
            $update_email = $inputs['email'];
            if (!empty($email) && $inputs['email'] == $email) {
                $update_email = null;
            }
            if (!empty($update_email)) {
                $user_id = User::pluckUserAttribute('user_uuid', $inputs['user_uuid'], 'id');
                $validation = Validator::make($inputs, UserValidationHelper::uniqueUserEmailRules($user_id[0])['rules'], UserValidationHelper::uniqueUserEmailRules($user_id[0])['message_' . strtolower($inputs['lang'])]);
                if ($validation->fails()) {
                    return ['success' => false, 'message' => $validation->errors()->first(), 'data' => []];
                }
            }
            $user_inputs['data']['email'] = !empty($update_email) ? $update_email : $inputs['email'];
        }
        return $user_inputs;
    }

    public static function servicesRules() {
        $validate['rules'] = [
            'service_uuid' => 'required'
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

}

?>
