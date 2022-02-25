<?php

namespace App\Helpers;

use App\User;
use Aws\Credentials\Credentials;
use Aws\Route53\Route53Client;
use Aws\Sns\SnsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Validator;
use App\PhoneNumberVerification;
use DB;
use App\Helpers\FreelancerHelper;
use App\Helpers\CustomerHelper;
use Illuminate\Support\Facades\Hash;
use Auth;
use phpDocumentor\Reflection\Types\Self_;
use Aws\Credentials\CredentialProvider;

Class VerificationHelper {
    /*
      |--------------------------------------------------------------------------
      | VerificationHelper that contains all the verification related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use verification processes
      |
     */

    /**
     * Description of VerificationHelper
     *
     * @author ILSA Interactive
     */

    /**
     * render view to add category.
     *
     * @return mixed
     */
    public static function getCode($inputs = []) {
        if (empty($inputs['login_user_type'])) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["missing_login_user_type"]);
        }

        //send only message
        return self::validatePhoneParametersForCode($inputs);

        if (empty($inputs['email']) && !empty($inputs['phone_number'])) {
            return self::validatePhoneParametersForCode($inputs);
        } elseif (!empty($inputs['email']) && empty($inputs['phone_number'])) {
            return self::validateEmailParametersForCode($inputs);
        } elseif (!empty($inputs['email']) && !empty($inputs['phone_number'])) {
            self::validatePhoneParametersForCode($inputs);
            return self::validateEmailParametersForCode($inputs);
        }
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function getCodeForLogin($inputs = []) {
        $validation = Validator::make($inputs, PhoneValidationHelper::codeForLoginRules()['rules'], PhoneValidationHelper::codeForLoginRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $check_existence = User::checkUser('phone_number', $inputs['phone_number']);
        if ((empty($check_existence)) || ($check_existence['profile_type'] != $inputs['login_user_type'])) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["user_existence_error"]);
        }
        return self::processVerfictionCode($inputs);
    }

    /**
     * validate Parameters For Code method
     * @param type $inputs
     * @return type
     */
    public static function validatePhoneParametersForCode($inputs) {
//        $validation = Validator::make($inputs, PhoneValidationHelper::phoneValidationRules()['rules'], PhoneValidationHelper::phoneValidationRules()['message_' . strtolower($inputs['lang'])]);
//        if ($validation->fails()) {
//            return CommonHelper::jsonErrorResponse($validation->errors()->first());
//        }
        // validation will be common for both customer and freelancer 
//        if ($inputs['verification_type'] == 'freelancer_signup') {
        $validation = Validator::make($inputs, PhoneValidationHelper::freelancerPhoneValidationRules()['rules'], PhoneValidationHelper::freelancerPhoneValidationRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
//        }
        $is_exist = PhoneNumberVerification::checkExisting('phone_number', $inputs['phone_number']);
        if (!empty($is_exist)) {
            PhoneNumberVerification::deleteRecordById($is_exist['id']);
        }
        return self::processVerfictionCode($inputs);
    }

    /**
     * process Verification Code method
     * @param type $inputs
     * @param type $select_update_type
     * @return type
     */
    public static function processVerfictionCode($inputs) {
        $isValid = PhoneValidationHelper::validatePhoneNumber($inputs);
        if (!$isValid['success']) {
            return CommonHelper::jsonErrorResponse($isValid['message']);
        }
        $inputs['verification_code'] = PhoneValidationHelper::getUniqueCode();
//        $inputs['verification_code'] = 1234;

        $message = PhoneValidationHelper::prepareMessageText($inputs, $inputs['verification_code']);
        $confirmation['phone_number'] = $inputs['phone_number'];
        $confirmation['message'] = $message;
        $inputs['message'] = $message;
        $confirmation['verification_code'] = $inputs['verification_code'];

        // remover these line when you need realt time sms;

        $smsStatus = ['@metadata' => ['statusCode' => 200]];

        // uncomment these lines when you need real time sms
        $smsStatus = self::sendSms($inputs);
        if (isset($smsStatus['@metadata']['statusCode']) && $smsStatus['@metadata']['statusCode'] == 200) {
            $save_code = self::processVerfictionCodeCont($inputs);
            if (empty($save_code) || !$save_code) {
                return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
            }
            DB::commit();
            return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verifcation_code_sent"]);
        } else {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
        }

//        $message_resp = self::sendVerificationMessage($inputs);
//        if ($message_resp === false){
//            DB::rollBack();
//            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
//            }
//        if (isset($inputs['phone_number'])) {
//            return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verifcation_code_sent"]);
//            $result = PhoneValidationHelper::sendSms($confirmation);
//            if ($result['success']) {
////                $response = PhoneValidationHelper::prepareVerificationCodeResponse($save_code);
//                return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verifcation_code_sent"]);
//            }
//            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["code_sent_error"]);
//        }
//        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function sendSms($params) {

        $snsclient = new SnsClient([
            'region' => 'ap-south-1',
            'version' => '2010-03-31',
            'credentials' => [
                'key' => 'AKIAU3J43LNMPD6MJUIC',
                'secret' => 'TEPhys4ISHsckvbr8tlCVmBGEqY9PkatAiZs6Iid',
            ]
        ]);
        $message = '[Boatek]' . ' ' . $params['verification_code'] . ' is your verification code. Do not share with anyone';
        $phone = $params['phone_number'];

        try {
            $result = $snsclient->publish([
                'Message' => $message,
                'PhoneNumber' => $phone,
            ]);
            return $result;
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }

    public static function sendVerificationMessage($inputs) {

        try {
            $inputs['phone_number'] = str_replace("+", "", $inputs['phone_number']);
            $body = [
                'form_params' => [
                    'username' => 'circlhttp',
                    'password' => 'icxlv3965ICX',
                    'to' => $inputs['phone_number'],
                    'from' => 'Circl',
                    'text' => $inputs['message']
                ]
            ];
            if (isset($inputs['lang']) && strtolower($inputs['lang'] == 'ar')) {
                $body['form_params']['coding'] = 3;
            }
            $client = new Client();
            $res = $client->request('POST', 'https://meapi.myvfirst.com/smpp/sendsms', $body);
            $res = $res->getBody()->getContents();

            return json_decode($res, true);
        } catch (GuzzleException $ex) {
            return false;
        }
    }

    /**
     * process Verification Code Count method
     * @param array $inputs
     * @return array
     */
    public static function processVerfictionCodeCont($inputs) {
        $save_inputs['phone_number'] = $inputs['phone_number'];
        $save_inputs['country_code'] = $inputs['country_code'];
        $save_inputs['country_name'] = $inputs['country_name'];
        $save_inputs['verification_code'] = $inputs['verification_code'];
        $save_inputs['code_expires_at'] = now()->addMinutes(config('general.globals.code_expire_time'));
        $save_inputs['code_uuid'] = UuidHelper::generateUniqueUUID("phone_number_verifications", "code_uuid");
        $save_code = PhoneNumberVerification::saveConfirmationCode($save_inputs);
        if ($save_code) {
            return $save_code;
        }
        return [];
    }

    /**
     * validate Email Parameters For Code method
     * @param type $inputs
     * @return type
     */
    public static function validateEmailParametersForCode($inputs) {
        if ($inputs['verification_type'] == 'freelancer_signup') {
            $validation = Validator::make($inputs, PhoneValidationHelper::emailValidationRules("freelancers")['rules'], PhoneValidationHelper::emailValidationRules("freelancers")['message_' . strtolower($inputs['lang'])]);
        } elseif ($inputs['verification_type'] == 'customer_signup') {
            $validation = Validator::make($inputs, PhoneValidationHelper::emailValidationRules("customers")['rules'], PhoneValidationHelper::emailValidationRules("customers")['message_' . strtolower($inputs['lang'])]);
        } elseif ($inputs['verification_type'] == 'forget_password') {
            if ($inputs['login_user_type'] == 'freelancer') {
                $validation = Validator::make($inputs, PhoneValidationHelper::forgetEmailValidationRules("freelancers")['rules'], PhoneValidationHelper::forgetEmailValidationRules("freelancers")['message_' . strtolower($inputs['lang'])]);
            } elseif ($inputs['login_user_type'] == 'customer') {
                $validation = Validator::make($inputs, PhoneValidationHelper::forgetEmailValidationRules("customers")['rules'], PhoneValidationHelper::forgetEmailValidationRules("customers")['message_' . strtolower($inputs['lang'])]);
            }
        }
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
//        $is_exist = PhoneNumberVerification::checkExisting('email', $inputs['email']);
//        if (!empty($is_exist)) {
//            PhoneNumberVerification::deleteRecordById($is_exist['id']);
//        }
        $code = PhoneValidationHelper::getUniqueCode();
        $message = PhoneValidationHelper::prepareMessageText($inputs, $code);
        $inputs['code_uuid'] = UuidHelper::generateUniqueUUID("phone_number_verifications", "code_uuid");
        $get_existing_code = PhoneNumberVerification::getTypeBasedCode($inputs['email'], $inputs['verification_type']);
        if (!empty($get_existing_code)) {
            $delete_existing_code = PhoneNumberVerification::deleteRecord('email', $inputs['email'], $inputs['verification_type']);
            if (!$delete_existing_code) {
                DB::rollBack();
                return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
            }
        }
        $save_code = PhoneNumberVerification::saveConfirmationCode(['code_uuid' => $inputs['code_uuid'], 'type' => $inputs['verification_type'], 'email' => $inputs['email'], 'verification_code' => $code, 'code_expires_at' => now()->addMinutes(config('general.globals.code_expire_time'))]);
        if ($save_code) {
            self::sendVerificationCodeEmail($inputs, $code, $message);
            DB::commit();
            return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verifcation_code_sent"]);
        }
        DB::rollBack();
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function sendVerificationCodeEmail($inputs, $code, $message, $subject = 'Circl Verification Code') {
        $data = [];
        $data['subject'] = $subject;
        $data['email'] = $inputs['email'];
        $data['code'] = $code;
        $data['message'] = $message;
        $data['template'] = "emails.code_email";
        $send_email = EmailSendingHelper::processCodeEmail($data);
//        CommonHelper::send_email('code_email', $data);
    }

    public static function verifyCode($inputs) {

        //return self::emailVerification($inputs);
        if (empty($inputs['email']) && !empty($inputs['phone_number'])) {
            return self::emailVerification($inputs);
        } elseif (!empty($inputs['email']) && empty($inputs['phone_number'])) {
            return self::emailVerification($inputs);
        } elseif (!empty($inputs['email']) && !empty($inputs['phone_number'])) {
            return self::emailVerification($inputs);
        }
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
    }

    public static function phoneNumberVerification($inputs) {
        $validation = Validator::make($inputs, PhoneValidationHelper::phoneCodeValidationRules()['rules'], PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['verification_code'] = PhoneValidationHelper::convert2english($inputs['verification_code']);
        $phone_data = PhoneNumberVerification::getConfirmationCode('phone_number', $inputs['phone_number'], $inputs);
        if (empty($phone_data)) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verification_code_invalid"]);
        }
        $code_inputs = ['code_uuid' => $phone_data['code_uuid'], 'status' => 'verified'];
        $update_code = PhoneNumberVerification::updateConfirmationCode('code_uuid', $phone_data['code_uuid'], $code_inputs);
        if (!$update_code) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["update_code_error"]);
        }
        return self::processCodeVerification($inputs);
    }

    public static function processCodeVerification($inputs) {

        if ($inputs['verification_type'] == "freelancer_signup") {


//            $inputs['onboard_count'] = 2;
//            $freelancer_inputs = ['freelancer_uuid' => $inputs['profile_uuid'], 'onboard_count' => 2, 'lang' => $inputs['lang']];
//            return FreelancerHelper::updateFreelancer($freelancer_inputs);
            return FreelancerHelper::signupProcess($inputs);
        } elseif ($inputs['verification_type'] == "customer_signup") {
            // customer code will go here
//            $inputs['onboard_count'] = 2;
            return CustomerHelper::customerSignup($inputs);
        } elseif ($inputs['verification_type'] == "customer_login") {
            return LoginHelper::userLoginWithPhone($inputs);
        } elseif ($inputs['verification_type'] == "freelancer_login") {
            return LoginHelper::userLoginWithPhone($inputs);
        } elseif ($inputs['verification_type'] == "forget_password") {
            return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["code_verifcation_success"]);
        } else {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["invalid_verification_type_error"]);
        }
    }

    public static function forgetPassword($inputs = []) {
        $validation = Validator::make($inputs, PhoneValidationHelper::forgetPasswordRules()['rules'], PhoneValidationHelper::forgetPasswordRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        if (($inputs['login_user_type'] != 'freelancer') && $inputs['login_user_type'] != 'customer') {
            return CommonHelper::jsonErrorResponse('User type is incorrect');
        }
        if (!empty($inputs['phone_number'])) {
            return self::validateEmailParametersForProcess($inputs);
        }
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function validateEmailParametersForProcess($inputs) {
//        $get_model = FreelancerHelper::getUserTypeDetail($inputs);
//        $updateMethod = 'check' . ucfirst($inputs['login_user_type']);
//        $is_exist = $get_model::$updateMethod('phone_number', $inputs['phone_number']);
        $is_exist = User::checkUser('phone_number', $inputs['phone_number']);
        if (empty($is_exist)) {
            return CommonHelper::jsonErrorResponse('The user does not exist');
        }
        $inputs['name'] = $is_exist['first_name'];
        $inputs['email'] = $is_exist['email'];
//        $code = PhoneValidationHelper::getUniqueCode();
        $code = 1234;
        $message = PhoneValidationHelper::prepareMessageText($inputs, $code);
        $inputs['code_uuid'] = UuidHelper::generateUniqueUUID("phone_number_verifications", "code_uuid");
        $get_existing_code = PhoneNumberVerification::getTypeBasedCodeByPhone($inputs['phone_number'], $inputs['verification_type']);
        if (!empty($get_existing_code)) {
            $delete_existing_code = PhoneNumberVerification::deleteRecord('phone_number', $inputs['phone_number'], $inputs['verification_type']);
            if (!$delete_existing_code) {
                return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
            }
        }
        $save_code = PhoneNumberVerification::saveConfirmationCode(['code_uuid' => $inputs['code_uuid'], 'phone_number' => $inputs['phone_number'], 'email' => $inputs['email'], 'verification_code' => $code, 'type' => $inputs['verification_type'], 'code_expires_at' => now()->addMinutes(config('general.globals.code_expire_time'))]);
        if ($save_code) {
            //self::sendForgetPasswordCodeEmail($inputs, $code, $message);
            $message_content = ['lang' => $inputs['lang'], 'message' => $message, 'phone_number' => $inputs['phone_number']];
            self::sendVerificationMessage($message_content);
            DB::commit();
            return CommonHelper::jsonSuccessResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verifcation_code_sent"]);
        }
        DB::rollBack();
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function resetPassword($inputs = []) {
        $validation = Validator::make($inputs, PhoneValidationHelper::resetPasswordRules()['rules'], PhoneValidationHelper::resetPasswordRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        if (($inputs['login_user_type'] != 'freelancer') && $inputs['login_user_type'] != 'customer') {
            return CommonHelper::jsonErrorResponse('User type is incorrect');
        }
//        $is_exist = PhoneNumberVerification::checkExisting('verification_code', $inputs['code']);
//        if (empty($is_exist)) {
//            return CommonHelper::jsonErrorResponse('code is incorrect');
//        }
        if (!empty($inputs['phone_number'])) {
            return self::resetFreelancerPasswordProcess($inputs);
        }
        return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["save_code_error"]);
    }

    public static function resetFreelancerPasswordProcess($inputs) {
//        if ($inputs['login_user_type'] == 'freelancer') {
//            if (Auth::guard('freelancer')->attempt(['phone_number' => $inputs['phone_number'], 'password' => $inputs['new_password']])) {
//                return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['same_password_error']);
//            }
//        } elseif ($inputs['login_user_type'] == 'customer') {
//            if (Auth::guard('customer')->attempt(['phone_number' => $inputs['phone_number'], 'password' => $inputs['new_password']])) {
//                return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['same_password_error']);
//            }
//        }
        if (Auth::attempt(['phone_number' => $inputs['phone_number'], 'password' => $inputs['new_password']])) {
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['same_password_error']);
        }
//        $get_model = FreelancerHelper::getUserTypeDetail($inputs);
//        $updateMethod = 'update' . ucfirst($inputs['login_user_type']);
//        $update = $get_model::$updateMethod('phone_number', $inputs['phone_number'], ['password' => Hash::make($inputs['new_password'])]);
        $update = User::updateUser('phone_number', $inputs['phone_number'], ['password' => Hash::make($inputs['new_password'])]);
        if (!$update) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['change_password_error']);
        }
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['change_password_success']);
    }

    public static function sendForgetPasswordCodeEmail($inputs, $code, $message, $subject = 'Circl Reset Password Code') {
        $data = [];
        $data['subject'] = $subject;
        $data['email'] = $inputs['email'];
        $data['name'] = $inputs['name'];
        $data['code'] = $code;
        $data['message'] = $message;
        $data['template'] = "emails.resetpassword";
        $send_email = EmailSendingHelper::processCodeEmail($data);
//        CommonHelper::send_email('resetpassword', $data);
    }

    public static function emailVerification($inputs) {
        $validation = Validator::make($inputs, PhoneValidationHelper::emailCodeValidationRules()['rules'], PhoneValidationHelper::emailCodeValidationRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['verification_code'] = PhoneValidationHelper::convert2english($inputs['verification_code']);
        $verification_data = PhoneNumberVerification::getConfirmationCode('phone_number', $inputs['phone_number'], $inputs);
        if (empty($verification_data)) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::emailCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verification_code_invalid"]);
        }

        if ($verification_data['code_expires_at'] <= now()) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::emailCodeValidationRules()['message_' . strtolower($inputs['lang'])]["verification_code_expires"]);
        }
        $code_inputs = ['code_uuid' => $verification_data['code_uuid'], 'status' => 'verified'];

        $update_code = PhoneNumberVerification::updateConfirmationCode('code_uuid', $verification_data['code_uuid'], $code_inputs);
        if (!$update_code) {
            return CommonHelper::jsonErrorResponse(PhoneValidationHelper::phoneCodeValidationRules()['message_' . strtolower($inputs['lang'])]["update_code_error"]);
        }

        return self::processCodeVerification($inputs);
    }

}

?>
