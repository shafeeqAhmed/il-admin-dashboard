<?php

namespace App\Helpers;

use App\Appointment;
use App\BankDetail;
use App\BoatekSetting;
use App\ClassBooking;
use App\Classes;
use App\PaymentDue;
use App\PaymentRequest;
use App\Purchases;
use App\Subscription;
use App\SubscriptionSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use DB;
use App\FreelancerTransaction;
use App\Freelancer;
use App\User;

Class BankHelper {
    /*
      |--------------------------------------------------------------------------
      | BankHelper that contains all the exception related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper Bank related stuff
      |
     */

    public static function updateBankDetail($inputs = []) {
        $validation = Validator::make($inputs, BankDetailValidationHelper::bankDetailRules()['rules'], BankDetailValidationHelper::bankDetailRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        if ($inputs['location_type'] == "UK" && empty($inputs['sort_code'])) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['sort_code_error']);
        }
        $save_data = self::prepareBankDetail($inputs);
        $inputs['user_id'] = CommonHelper::getUseridByUuid($inputs['logged_in_uuid']);
        $bank_detail = BankDetail::createorUpdateBankDetail('user_id', $inputs['user_id'], $save_data);
        $bank_detail_response = BankResponseHelper::setResponse($bank_detail);
        if ($bank_detail) {
            $update_freelancer = \App\User::updateUser('user_uuid', $inputs['logged_in_uuid'], ['has_bank_detail' => 1]);
//            if (!empty($inputs['profile_type'])) {
//                $update_freelancer = Freelancer::updateFreelancer('freelancer_uuid', $inputs['logged_in_uuid'], ['profile_type' => $inputs['profile_type']]);
//            }
            DB::commit();
            return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $bank_detail_response);
        }
        DB::rollback();
        return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
    }

    public static function prepareBankDetail($inputs = []) {
        $data = [];
        if (!empty($inputs)) {
            if ($inputs['location_type'] == "KSA") {
                if (isset($inputs['account_name'])) {
                    $data['account_name'] = $inputs['account_name'];
                }
                $data['iban_account_number'] = $inputs['iban_account_number'];
                $data['billing_address'] = !empty($inputs['billing_address']) ? $inputs['billing_address'] : null;
                $data['post_code'] = $inputs['post_code'];
                $data['account_title'] = !empty($inputs['account_title']) ? $inputs['account_title'] : null;
                $data['bank_name'] = !empty($inputs['bank_name']) ? $inputs['bank_name'] : null;
                $data['location_type'] = $inputs['location_type'];
            } elseif ($inputs['location_type'] == "UK") {
                $data['location_type'] = $inputs['location_type'];
                $data['account_name'] = $inputs['account_name'];
                $data['account_number'] = $inputs['iban_account_number'];
                $data['billing_address'] = !empty($inputs['billing_address']) ? $inputs['billing_address'] : null;
                $data['sort_code'] = $inputs['sort_code'];
                $data['post_code'] = !empty($inputs['post_code']) ? $inputs['post_code'] : null;
            }
        }
        return $data;
    }

    public static function getOverviewBankDetail($inputs = []) {

        $validation = Validator::make($inputs, BankDetailValidationHelper::overviewBankDetailRules()['rules'], BankDetailValidationHelper::overviewBankDetailRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }

        $check_user = User::checkUser('user_uuid', $inputs['logged_in_uuid']);
        if (empty($check_user)) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
//        $inputs['user_id'] = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid'], 'user_id');
        $inputs['user_id'] = $check_user['id'];
//        $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']);
//        $inputs['from_currency'] = !empty($check_freelancer['freelancer_categories'][0]['currency']) ? $check_freelancer['freelancer_categories'][0]['currency'] : $check_freelancer['default_currency'];
//        $inputs['to_currency'] = $check_freelancer['default_currency'];
        //$earnings = FreelancerTransaction::calculateEarnings('freelancer_uuid', $inputs['freelancer_uuid'], ['status' => 'confirmed']);
//        $appointment_amount = Appointment::getAppointmentsRevenue('freelancer_uuid', $inputs['freelancer_uuid']);
//        $class_amount = Classes::getClassRevenue('freelancer_uuid', $inputs['freelancer_uuid']);
//        $subscription = SubscriptionSetting::getSubscriptionRevenue('freelancer_uuid', $inputs['freelancer_uuid']);
//        $subscription_amount = self::getSubscriptionAmount($subscription);
//        $amount = $appointment_amount + $class_amount + $subscription_amount;
        //$amount = $earnings - ($earnings * (CommonHelper::$circle_commission['commision_rate_percentage'] / 100));
        //$response['total_amount'] = CommonHelper::getConvertedCurrency($amount, $inputs['from_currency'], $inputs['to_currency']);
        $response['total_amount'] = null;
//        $response['total_amount'] = PaymentDue::getUserTotalEarnings($inputs, false);
//        $response['completed_withdraw'] = PaymentRequest::getPaymentRequestAmount(2, $inputs['user_id']);
        $response['completed_withdraw'] = null;
        $response['requested_withdraw'] = null;
//                $response['processed_withdraw'] = PaymentRequest::getPaymentRequestAmount(1, $inputs['user_id']);
        $response['processed_withdraw'] = null;
//        $response['requested_withdraw'] = PaymentRequest::getPaymentRequestAmount(0, $inputs['user_id']);
        $freelancer_id =
        $response['pending_withdraw'] =PurchaseHelper::getFreelancerBalance($inputs['freelancer_uuid'],'pending');
//        $response['pending_withdraw'] = PaymentDue::getUserPendingBalance($inputs);
//        $response['pending_withdraw'] = round($response['pending_withdraw'], 2);
//        $response['available_withdraw'] = self::calculateFreelancerAvailableWithdraw($inputs, $response);
        $response['available_withdraw'] = PurchaseHelper::getFreelancerBalance($inputs['freelancer_uuid'],'succeeded');
        $boatek_settings =  BoatekSetting::getBoatekSetting();
        $response['boatek_commission_charges'] = !empty($boatek_settings) ? $boatek_settings['boatek_commission_charges'] : '';
        $response['transaction_charges'] = !empty($boatek_settings) ? $boatek_settings['transaction_charges'] : '';
        $bank_detail = BankDetail::getBankDetail('user_id', $inputs['user_id']);

        $response['bank_detail'] = !empty($bank_detail) ? BankResponseHelper::setResponse($bank_detail) : null;
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }


    public static function calculateFreelancerAvailableWithdraw($inputs, $response) {
        $total_payment_dues = PaymentDue::getUserTotalEarnings($inputs, true);
        $total_payment_dues = $total_payment_dues - ($response['completed_withdraw'] + $response['requested_withdraw'] + $response['processed_withdraw']);
        $total_payment_dues = $total_payment_dues < 0 ? 0 : $total_payment_dues;
        return $total_payment_dues;
    }

    public static function getSubscriptionAmount($subscription) {
        $amount = 0;
        if (!empty($subscription)) {
            foreach ($subscription as $sub) {
                $amount += $sub['price'] * $sub['subscriptions_count'];
            }
        }
        return $amount;
    }

    public static function getAllTransactions($inputs = []) {
        $validation = Validator::make($inputs, BankDetailValidationHelper::overviewBankDetailRules()['rules'], BankDetailValidationHelper::overviewBankDetailRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        /* $search_params['status'] = isset($inputs['type']) ? $inputs['type'] : null;
          if ($search_params['status'] == 'completed') {
          $search_params['status'] = 'credited';
          }
          $search_params['limit'] = isset($inputs['limit']) ? $inputs['limit'] : null;
          $search_params['offset'] = isset($inputs['offset']) ? $inputs['offset'] : null; */

// need to update this logic after payments scenario

        if ($inputs['login_user_type'] == 'freelancer') {
            $freelancer_id = CommonHelper::getFreelancerIdByUuid($inputs['logged_in_uuid']);
//            if (isset($inputs['type']) && strtolower($inputs['type']) != 'all') {
//                $data = Purchases::getPurchasesList('freelancer',$freelancer_id,'all',['status' =>$inputs['type']],$inputs['limit'],$inputs['offset']);
//            } else {
//                $data = Purchases::getPurchasesList('freelancer',$freelancer_id,'all',[],$inputs['limit'],$inputs['offset']);
//            }

            if (isset($inputs['type']) && strtolower($inputs['type']) == 'pending') {
                $data = Appointment::getFreelancerAppointments($freelancer_id);
            } elseif (isset($inputs['type']) && strtolower($inputs['type']) == 'available') {
                $data = Appointment::getFreelancerAppointments($freelancer_id);
            } else {
                $data = Appointment::getFreelancerAppointments($freelancer_id);
            }
        }


        if ($inputs['login_user_type'] == 'customer') {
            $customer = \App\Customer::getSingleCustomerDetail('customer_uuid', $inputs['logged_in_uuid']);

//            if (isset($inputs['type']) && strtolower($inputs['type']) != 'all') {
//                $data = Purchases::getPurchasesList('freelancer',$customer['id'],'all',['status' =>$inputs['type']],$inputs['limit'],$inputs['offset']);
//            } else {
//                $data = Purchases::getPurchasesList('freelancer',$customer['id'],'all',[],$inputs['limit'],$inputs['offset']);
//            }
            if (!empty($inputs['type']) && strtolower($inputs['type']) == 'all') {
                $data = Appointment::getCustomerAppointments($customer['id']);
            } else {
                $data = Appointment::getCustomerAppointments($customer['id']);
            }
        }
        $response = [];
        $response = BankResponseHelper::setTransactionResposne($data, $inputs);
        if (isset($inputs['type']) && strtolower($inputs['type']) == 'available' && !empty($response)) {
            $response = self::filterRecords($response);
        }

        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], array_values($response));
    }

    public static function filterRecords($records) {
        $response = [];

        foreach ($records as $record) {
            if ($record['session_status'] == 'completed') {
                $response[] = $record;
            }
        }
        return $response;
    }

    public static function getTransactionDetail($inputs = []) {
        $validation = Validator::make($inputs, BankDetailValidationHelper::getTransactionDetailRules()['rules'], BankDetailValidationHelper::getTransactionDetailRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
//        $transaction_data = FreelancerTransaction::getTransactionDetail('freelancer_transaction_uuid', [$inputs['uuid']]);
        $transaction_data = Purchases::getPurchaseDetail('purchases_uuid',$inputs['uuid']);
        if (empty($transaction_data)) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }
        $response = BankResponseHelper::setTransactionDetailResponse($transaction_data, $inputs);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function getCurrencyRate($inputs) {
        //$get_currency_rates = HyperpayHelper::getCurrencyRate();
        $get_currency_rates = CommonHelper::currencyConversionRequest($inputs['from'], $inputs['to']);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $get_currency_rates);
    }

    public static function getWithdrawRequests($inputs = []) {
        $validation = Validator::make($inputs, BankDetailValidationHelper::getWithdrawRequestsRules()['rules'], BankDetailValidationHelper::getWithdrawRequestsRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $limit = isset($inputs['limit']) ? $inputs['limit'] : null;
        $offset = isset($inputs['offset']) ? $inputs['offset'] : null;
        $data = PaymentRequest::getPaymentRequests('user_uuid', $inputs['logged_in_uuid'], $limit, $offset, $inputs['type']);
        $response = BankResponseHelper::preparePaymentRequestResposne($data, $inputs['local_timezone']);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

}

?>
