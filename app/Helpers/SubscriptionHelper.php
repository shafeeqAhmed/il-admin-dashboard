<?php

namespace App\Helpers;

use App\MoyasarWebForm;
use App\SubscriptionSetting;
use App\Subscription;
use App\Customer;
use DB;
use Illuminate\Support\Facades\Validator;

Class SubscriptionHelper {
    /*
      |--------------------------------------------------------------------------
      | SubscriptionHelper that contains all the categpry related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use settings processes
      |
     */

    /**
     * Description of CategoryHelper
     *
     * @author ILSA Interactive
     */
    public static function addSubscription($inputs) {

        $validation = Validator::make($inputs, FreelancerValidationHelper::addSubscription()['rules'], FreelancerValidationHelper::addSubscription()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
//        $inputs['subscriber_uuid'], $inputs['subscribed_uuid']
        $inputs['subscriber_id'] = CommonHelper::getRecordByUuid('customers','customer_uuid',$inputs['subscriber_uuid']);
        $inputs['subscribed_id'] = CommonHelper::getRecordByUuid('freelancers','freelancer_uuid',$inputs['subscribed_uuid']);
        $inputs['subscription_settings_id'] = CommonHelper::getRecordByUuid('subscription_settings','subscription_settings_uuid',$inputs['subscription_settings_uuid']);

        $inputs['transaction_id'] = null;
        if (!empty($inputs['resource_path'])) {
            $transaction = HyperpayHelper::checkTransactionStatus($inputs);
            if (!$transaction['success']){
                return CommonHelper::jsonErrorResponse($transaction['message']);
            }
            $inputs['transaction_id'] = $transaction['transaction_id'];
            $inputs['payment_details'] = $transaction['payment_details'];
            if (!empty($transaction['registration_id'])) {
                $inputs['card_info'] = $transaction['payment_details']->card;
                $save_registaration_id = PaymentHelper::saveRegistrationId($inputs, $transaction['registration_id']);
                if (!$save_registaration_id['success']) {
                    return CommonHelper::jsonErrorResponse($save_registaration_id['message']);
                }
            } else {
                return CommonHelper::jsonErrorResponse("Payment type is not recurring");
            }
            $inputs['registration_id'] = $transaction['registration_id'];
        }
        elseif ( !empty($inputs['source_id']) ){
            $payment = MoyasarHelper::getPayment($inputs['source_id']);
            if (!$payment['success'] || empty($payment = $payment['payment'])):
                return CommonHelper::jsonErrorResponse('Invalid source id.');
            endif;
            if ($payment->status != 'paid'){
                return CommonHelper::jsonErrorResponse('Payment is not paid.');
            }
            $webForm = MoyasarWebForm::where([
                'profile_uuid' => $inputs['logged_in_uuid'],
                'moyasar_web_form_uuid' => $inputs['moyasar_web_form_uuid'] ?? '',
            ])->first();
            if (empty($webForm)):
                return CommonHelper::jsonErrorResponse('Web Form not found for logged in user.');
            endif;
            if ($webForm->amount != $payment->amount){
                return CommonHelper::jsonErrorResponse('Payment amount is not same as web form.');
            }
            $webForm->payment_id = $payment->id;
            $webForm->status = 'paid';
            $webForm->save();
            $inputs['transaction_id'] = $payment->id;
            $inputs['payment_details'] = $payment;
            $inputs['payment_processor'] = 'moyasar';
//            $inputs['paid_amount'] = $payment->amount / 100;
            $inputs['registration_id'] = '';
        }
        $subscription_setting = SubscriptionSetting::getSingleSubscriptionSetting('subscription_settings_uuid', $inputs['subscription_settings_uuid']);
        if (empty($subscription_setting)) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
        $subscription_term = $subscription_setting['type'] ?? 'monthly';
        $inputs['total_amount'] = CommonHelper::getConvertedCurrency($subscription_setting['price'], $subscription_setting['currency'], $inputs['currency']);
        $inputs['actual_amount'] = CommonHelper::getConvertedCurrency($subscription_setting['price'], $subscription_setting['currency'], $inputs['currency']);
        $inputs['from_currency'] = $subscription_setting['currency'];
        $inputs['to_currency'] = $inputs['currency'];
        $inputs['exchange_rate'] = config('general.globals.' . $inputs['currency']);
//        $inputs['payment_brand'] = !empty($inputs['payment_details']->paymentBrand) ? $inputs['payment_details']->paymentBrand : null;
        $inputs['payment_brand'] = !empty($inputs['payment_details']->source) && !empty($inputs['payment_details']->source->type) ? $inputs['payment_details']->source->type : null;
        $inputs['moyasar_fee'] = !empty($inputs['payment_details']->fee) ? $inputs['payment_details']->fee : 0;
        $data = [
            'subscriber_id' => $inputs['subscriber_id'],
            'subscribed_id' => $inputs['subscribed_id'],
            'subscription_settings_id' => $inputs['subscription_settings_id'],
            'subscription_date' => date("Y-m-d H:i:s"),
            'subscription_end_date'=>date('Y-m-d H:i:s',self::setSubscriptionEndDate($subscription_setting['type'])),// in this fun we set the end date of the subscription package
            'transaction_id' =>$inputs['transaction_id'],
            'card_registration_id' => $inputs['registration_id'] ?? '',
        ];

        $check = Subscription::checkSubscriber($inputs['subscriber_id'], $inputs['subscribed_id']);
        if ($check) {
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['already_subscribed_error']);
        }
        $save = Subscription::createSubscription($data);
        $inputs['subscription_uuid'] = $save['subscription_uuid'];
        if (!$save) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['add_transaction_log_error']);
        }
        $save_transaction = TransactionHelper::saveSubscriptionTransaction($inputs);
        if (!$save_transaction) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['add_transaction_error']);
        }

        $saveTransactionPaymentDue = TransactionHelper::saveTransactionPaymentDue($save_transaction->toArray(), $subscription_term, $save['subscription_date']);
        if (!$saveTransactionPaymentDue) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['add_transaction_request_due_error']);
        }
        ProcessNotificationHelper::sendSubscriberNotification($inputs, $save);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request']);
    }
    public static function setSubscriptionEndDate($type){

        if($type == 'monthly'){
            return  strtotime('+1 month', strtotime(date('Y-m-d H:i:s')));
        }elseif($type == 'quarterly'){
            return strtotime('+3 month', strtotime(date('Y-m-d H:i:s')));
        }else{
            return strtotime('+12 month', strtotime(date('Y-m-d H:i:s')));
        }
    }
    public static function addSubscriptionSettings($inputs) {
        if (empty($inputs['settings'])) {
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['invalid_data_error']);
        }

        foreach ($inputs['settings'] as $key => $setting) {
            $setting['freelancer_id'] = $inputs['freelancer_uuid'];
            $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid( $inputs['freelancer_uuid']);
            $validation = Validator::make($setting, FreelancerValidationHelper::addSubscriptionSettings()['rules'], FreelancerValidationHelper::addSubscriptionSettings()['message_' . strtolower($inputs['lang'])]);
            if ($validation->fails()) {
                return CommonHelper::jsonErrorResponse($validation->errors()->first());
            }
            $data[$key] = ['subscription_settings_uuid' => UuidHelper::generateUniqueUUID('subscription_settings', 'subscription_settings_uuid'),
                'freelancer_id' => $inputs['freelancer_id'],
                'type' => $setting['type'],
                'price' => $setting['price'],
                'currency' => $setting['currency']];
            $validation = Validator::make($data[$key], FreelancerValidationHelper::addSubscriptionSettings()['rules'], FreelancerValidationHelper::addSubscriptionSettings()['message_' . strtolower($inputs['lang'])]);
            if ($validation->fails()) {
                return CommonHelper::jsonErrorResponse($validation->errors()->first());
            }
        }
        $add_settings = SubscriptionSetting::saveSubscriptionSetting($data);
        if (!$add_settings) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
        }

        $profile_inputs = ['freelancer_uuid' => $inputs['freelancer_uuid'], 'has_subscription' => 1, 'lang' => $inputs['lang']];
        if (array_key_exists('profile_type', $inputs) && !empty($inputs['profile_type'])) {
            $profile_inputs['profile_type'] = $inputs['profile_type'];
        }
        if (isset($inputs['receive_subscription_request'])) {
            $profile_inputs['receive_subscription_request'] = $inputs['receive_subscription_request'];
        }
        $save_profile = FreelancerHelper::updateFreelancer($profile_inputs);
        $result = json_decode(json_encode($save_profile));
        if (!$result->original->success) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
        }
        $subscriptions = SubscriptionSetting::getFreelancerSubscriptions('freelancer_id', $inputs['freelancer_id']);
        $response = FreelancerResponseHelper::makeFreelancerSucscriptionArr($subscriptions);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function updateFreelancerSubscriptionSettings($inputs) {
        $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']);
        if (!empty($inputs['settings'])) {

            foreach ($inputs['settings'] as $setting) {

                if (!empty($setting['subscription_settings_uuid'])) {
                    $update_data = [
                        'subscription_settings_uuid' => $setting['subscription_settings_uuid'],
                        'type' => $setting['type'],
                        'price' => $setting['price'],
                        'currency' => $setting['currency'],

                    ];

                    $validation = Validator::make($update_data, FreelancerValidationHelper::updateSubscriptionSettings()['rules'], FreelancerValidationHelper::updateSubscriptionSettings()['message_' . strtolower($inputs['lang'])]);
                    if ($validation->fails()) {
                        return CommonHelper::jsonErrorResponse($validation->errors()->first());
                    }

                    $update_settings = SubscriptionSetting::updateSubscriptionSetting('subscription_settings_uuid', $update_data['subscription_settings_uuid'], $update_data);

                    if (!$update_settings) {
                        DB::rollBack();
                        return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
                    }
                }

                else {
                    $add_data = ['subscription_settings_uuid' => UuidHelper::generateUniqueUUID('subscription_settings', 'subscription_settings_uuid'),
                        'freelancer_id' => $inputs['freelancer_id'],
                        'type' => $setting['type'],
                        'price' => $setting['price'],
                        'currency' => $setting['currency']
                    ];

                    $validation = Validator::make($add_data, FreelancerValidationHelper::addSubscriptionSettings()['rules'], FreelancerValidationHelper::addSubscriptionSettings()['message_' . strtolower($inputs['lang'])]);
                    if ($validation->fails()) {
                        return CommonHelper::jsonErrorResponse($validation->errors()->first());
                    }
                    $add_settings = SubscriptionSetting::createSubscriptionSetting($add_data);
                    if (empty($add_settings)) {
                        DB::rollBack();
                        return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
                    }
                }

            }
        }


        if (!empty($inputs['delete_setting'])) {
            foreach ($inputs['delete_setting'] as $delete_setting) {
                $delete_setting_data = ['subscription_settings_uuid' => $delete_setting['subscription_settings_uuid'], 'is_archive' => 1, 'freelancer_id' => $inputs['freelancer_id']];
                $delete_settings = SubscriptionSetting::updateSubscriptionSetting('subscription_settings_uuid', $delete_setting_data['subscription_settings_uuid'], $delete_setting_data);
                if (!$delete_settings) {
                    DB::rollBack();
                    return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
                }
            }
        }



        $check_subscriptions = SubscriptionSetting::checkActiveSubscriptionSetting('freelancer_id', $inputs['freelancer_id']);
        if ($check_subscriptions) {
            $profile_inputs = ['freelancer_uuid' => $inputs['freelancer_uuid'], 'has_subscription' => 1, 'lang' => $inputs['lang']];
        } elseif (!$check_subscriptions) {
            $profile_inputs = ['freelancer_uuid' => $inputs['freelancer_uuid'], 'has_subscription' => 0, 'lang' => $inputs['lang']];
        }
        if (isset($inputs['receive_subscription_request'])) {
            $profile_inputs['receive_subscription_request'] = $inputs['receive_subscription_request'];
        }

        $save_profile = FreelancerHelper::updateFreelancer($profile_inputs);

        $result = json_decode(json_encode($save_profile));
        if (!$result->original->success) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_settings_error']);
        }
        $subscriptions = SubscriptionSetting::getFreelancerSubscriptions('freelancer_id', $inputs['freelancer_id']);
        $response = FreelancerResponseHelper::makeFreelancerSucscriptionArr($subscriptions);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function processSubscription($inputs = []) {
        $validation = Validator::make($inputs, SubscriberValidationHelper::processSubscriptionRules()['rules'], SubscriberValidationHelper::processSubscriptionRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        if (strtolower($inputs['login_user_type']) != 'customer') {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
        $customer = Customer::checkCustomer('customer_uuid', $inputs['logged_in_uuid']);
        if (!$customer) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
        $check_subscription = Subscription::checkSubscription('subscription_uuid', $inputs['subscription_uuid']);
        if (!$check_subscription) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
        if ($inputs['type'] == 'cancel') {
            $data = ['auto_renew' => 0];
            $message = "has cancelled their subscription";
        } elseif ($inputs['type'] == 'activate') {
            $data = ['auto_renew' => 1];
            $message = "has activated their subscription";
        }
        $data = Subscription::cancelSubscription('subscription_uuid', $inputs['subscription_uuid'], $data);
        if (!$data) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData($type = 'error', $inputs['lang'])['success_error']);
        }
        $text = ($inputs['type'] == "cancel") ? "cancelled" : "activated";
        $message = !empty($message) ? "has " . $text . " their subscription" : null;
        $notification_data = ['subscriber_uuid' => $check_subscription['subscriber_uuid'], 'subscribed_uuid' => $check_subscription['subscribed_uuid'], 'subscription_uuid' => $inputs['subscription_uuid'], 'message' => $message];
        ProcessNotificationHelper::updateSubscriptionNotification($notification_data);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request']);
    }

    public static function checkFreelancerSubscriptionSettingExists($data = []) {

        $check_settings = false;
        if (!empty($data)) {
            $get_settings = SubscriptionSetting::checkSubscriptionSetting('freelancer_id', $data['id']);
            $check_settings = !empty($get_settings) ? true : false;
        }
        return $check_settings ? true : false;
    }

}

?>
