<?php

namespace App\Helpers;

use App\Customer;
use App\User;
use App\WalkinCustomer;
use Illuminate\Support\Facades\Validator;
use DB;

Class WalkinCustomerHelper {
    /*
      |--------------------------------------------------------------------------
      | WalkinCustomerHelper that contains customer related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use customer processes
      |
     */

    /**
     * Description of WalkinCustomerHelper
     *
     * @author ILSA Interactive
     */
    public static function addWalkinCustomer($inputs = []) {
        $validation = Validator::make($inputs, WalkinCustomerValidationHelper::addCustomerRules()['rules'], WalkinCustomerValidationHelper::addCustomerRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['user_uuid'] = UuidHelper::generateUniqueUUID("users", "user_uuid");



        $walkin = self::makeParams($inputs);
        $save_user = User::Adduser($walkin);
        if (!$save_user) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(WalkinCustomerValidationHelper::addCustomerRules()['message_' . strtolower($inputs['lang'])]['save_error']);
        }

        $customer['customer_uuid'] = UuidHelper::generateUniqueUUID("customers", "customer_uuid");
        $customer['user_id'] = $save_user['id'];

        $save_customer = Customer::saveCustomer($customer);

        if (!$save_customer) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }

        $save_user['customer_uuid'] = $save_customer['customer_uuid'];
        $response = WalkinCustomerResponseHelper::WalkinCustomerResponse($save_user);

        DB::commit();
        return CommonHelper::jsonSuccessResponse(WalkinCustomerValidationHelper::addCustomerRules()['message_' . strtolower($inputs['lang'])]['save_success'], $response);
    }

    public static function makeParams($inputs){
//        $userId = (User::saveUser())['id'];
        return [
            'user_uuid'=>$inputs['user_uuid'],
            'freelancer_id'=>CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']),
//            'user_id'=> $userId,
            'first_name'=>$inputs['first_name'],
            'last_name'=>(isset($inputs['last_name']))?$inputs['last_name']:'',
            'is_verified'=>0,
            'is_active'=>1,
            'profile_type'=>'walkin_customer',
        ];
    }

}

?>
