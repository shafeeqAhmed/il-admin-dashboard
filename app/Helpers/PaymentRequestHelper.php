<?php

namespace App\Helpers;

use App\PaymentDue;
use Illuminate\Support\Facades\Validator;
use App\PaymentRequest;
use App\BankDetail;
use DB;

Class PaymentRequestHelper {
    /*
      |--------------------------------------------------------------------------
      | PaymentRequestHelper that contains payment request related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use payment request processes
      |
     */

    /**
     * Description of PaymentRequestHelper
     *
     * @author ILSA Interactive
     */
    public static function processPaymentRequest($inputs) {
        $validation = Validator::make($inputs, PaymentRequestValidationHelper::preparePaymentRequestRules()['rules'], PaymentRequestValidationHelper::preparePaymentRequestRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $validate_bank_details = BankDetail::getBankDetail('freelancer_uuid', $inputs['logged_in_uuid']);
        if (!$validate_bank_details) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(PaymentRequestValidationHelper::preparePaymentRequestRules()['message_' . strtolower($inputs['lang'])]['bank_detail_error']);
        }
        $request_inputs = self::processPreparePaymentRequestInput($inputs);

        $response_payment_request = PaymentRequest::savePaymentRequest($request_inputs);
        if (!$response_payment_request) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(PaymentRequestValidationHelper::preparePaymentRequestRules()['message_' . strtolower($inputs['lang'])]['add_payment_request_error']);
        }

        DB::commit();
        return CommonHelper::jsonSuccessResponseWithoutData(PaymentRequestValidationHelper::preparePaymentRequestRules()['message_' . strtolower($inputs['lang'])]['successful_request']);
    }

    public static function processPreparePaymentRequestInput($input) {
        $deduction_amount = strtolower($input['currency']) == 'pound' ? CommonHelper::$circle_commission['withdraw_pound_fee'] : CommonHelper::$circle_commission['withdraw_sar_fee'];
        $data = array(
            'user_uuid' => !empty($input['logged_in_uuid']) ? $input['logged_in_uuid'] : null,
            'requested_amount' => !empty($input['amount']) && $input['amount'] > 0 ? $input['amount'] : 0,
            'deductions' => $deduction_amount,
            'final_amount' => !empty($input['amount']) && $input['amount'] > 0 ? $input['amount'] - $deduction_amount : 0,
            'currency' => !empty($input['currency']) ? $input['currency'] : null,
            'notes_from_freelancer' => !empty($input['notes_from_freelancer']) ? $input['notes_from_freelancer'] : null
        );
        return $data;
    }

    public static function getRequestToken($inputs = []) {

        $requestParams = array(
            'service_command' => 'SDK_TOKEN',
            'access_code' => config("general.payfort.access_code"),
            'merchant_identifier' => config("general.payfort.merchant_identifier"),
            'language' => 'en',
            'device_id' => $inputs['device_id'],
        );
        $signature = self::prepareSignature($requestParams);
        $requestParams['signature'] = $signature;
        // test environment url
        $url = config("general.payfort.payfort_url");
        // production environment url
        $decoded_data = self::prepareCurlRequest($url, $requestParams);
        $response = !empty($decoded_data) ? self::prepareTokenResponse($decoded_data) : [];
//        $response['sdk_token'] = !empty($decoded_data->sdk_token) ? $decoded_data->sdk_token : null;
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request'], $response);
    }

    public static function prepareSignature($requestParams) {
        $shaString = '';
        ksort($requestParams);
        foreach ($requestParams as $key => $value) {
            $shaString .= $key . '=' . $value;
        }
        $shaString = config("general.payfort.SHA_REQUEST_PHRASE") . $shaString . config("general.payfort.SHA_REQUEST_PHRASE");
        $signature = hash("sha256", $shaString);
        return $signature;
    }

    public static function prepareTokenResponse($decoded_data) {
        $response['sdk_token'] = !empty($decoded_data->sdk_token) ? $decoded_data->sdk_token : null;
        $response['device_id'] = !empty($decoded_data->device_id) ? $decoded_data->device_id : null;
        return $response;
    }

    public static function prepareCurlRequest($url, $requestParams) {
        $ch = curl_init($url);
        $data = json_encode($requestParams);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $decoded_data = json_decode($result);
        return $decoded_data;
    }

    public static function saveAuthorizationData($inputs) {
        $validation = Validator::make($inputs, PaymentRequestValidationHelper::prepareAuthorizationRequestRules()['rules'], PaymentRequestValidationHelper::prepareAuthorizationRequestRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['customer_id'] = CommonHelper::getRecordByUuid('customers', 'customer_uuid', $inputs['logged_in_uuid']);
        // if remember me is true then save the new card
        if (($inputs['remember_me'] == 'true') && (empty($inputs['customer_card_uuid']))) {
            // save customer card
            $card_data = self::makeCustomerCardParams($inputs);
            $save_data = \App\CustomerCard::saveData($card_data);
            if (!$save_data) {
                DB::rollBack();
                return CommonHelper::jsonErrorResponse("error occurred while saving card");
            }
        }
//else if remember me is false and the card is not empty then delete the card from db as well as payfort
        elseif (($inputs['remember_me'] == 'false') && (!empty($inputs['customer_card_uuid']))) {
            // check if card exists in db 
            $save_data = \App\CustomerCard::updateData('customer_card_uuid', $inputs['customer_card_uuid'], ['is_archive' => 1]);
            if (!$save_data) {
                DB::rollBack();
                return CommonHelper::jsonErrorResponse("error occurred while updating details");
            }
        }
        // if remember me is true but customer card uuid is not empty then do not save card because it already exists in db
        $authorization_params = self::prepareAuthorizationParams($inputs);
        $save_authorization = \App\BookingAuthData::saveData($authorization_params);
        if (!$save_authorization) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse("error occurred while authorizing ");
        }

        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request']);
    }

    public static function getCardsList($inputs) {
        $validation = Validator::make($inputs, PaymentRequestValidationHelper::getCardListRules()['rules'], PaymentRequestValidationHelper::getCardListRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $customer_id = CommonHelper::getRecordByUuid('customers', 'customer_uuid', $inputs['logged_in_uuid']);

        $cards = \App\CustomerCard::getCustomerCards($customer_id);
        $response = self::prepareCardsResponse($cards);
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request'], $response);
    }

    public static function unAuthorizeRequest($inputs) {
        $requestParams = array(
            'command' => 'VOID_AUTHORIZATION',
            'access_code' => config('general.payfort.access_code'),
            'merchant_identifier' => config('general.payfort.merchant_identifier'),
            'merchant_reference' => $inputs['appointment_uuid'],
            'language' => 'en',
                // signature
        );
        $signature = self::prepareSignature($requestParams);
        $requestParams['signature'] = $signature;
        $url = config("general.payfort.payfort_url");
        // production environment url
        $decoded_data = self::prepareCurlRequest($url, $requestParams);
        return $decoded_data;
    }

    public static function prepareRefundCall($inputs) {
        $requestParams = array(
            'command' => 'REFUND',
            'access_code' => config('general.payfort.access_code'),
            'merchant_identifier' => config('general.payfort.merchant_identifier'),
            'merchant_reference' => $inputs['appointment_uuid'],
            'amount' => $inputs['refund_amount'],
            'currency' => 'SAR',
            'language' => 'en',
        );
        $signature = self::prepareSignature($requestParams);
        $requestParams['signature'] = $signature;
        $url = config("general.payfort.payfort_url");
        // production environment url
        $decoded_data = self::prepareCurlRequest($url, $requestParams);
        return $decoded_data;
    }

    public static function prepareCardsResponse($array) {
        $response = [];
        if (!empty($array)) {
            foreach ($array as $key => $data) {
                $response[$key]['customer_card_uuid'] = $data['customer_card_uuid'];
                $response[$key]['card_holder_name'] = $data['card_holder_name'];
                $response[$key]['card_name'] = $data['card_name'];
                $response[$key]['last_digits'] = $data['last_digits'];
                $response[$key]['expiry'] = $data['expiry'];
                $response[$key]['token'] = $data['token'];
            }
        }
        return $response;
    }

    public static function makeCustomerCardParams($params) {

        return [
            'customer_id' => $params['customer_id'],
            'card_id' => null,
            'token' => $params['token'],
            'card_name' => !empty($params['card_name']) ? $params['card_name'] : null,
            'card_type' => !empty($params['card_type']) ? $params['card_type'] : null,
            'last_digits' => !empty($params['last_digits']) ? $params['last_digits'] : null,
            'card_holder_name' => !empty($params['card_holder_name']) ? $params['card_holder_name'] : null,
            'expiry' => !empty($params['expiry']) ? $params['expiry'] : null,
            'customer_checkout_id' => null,
            'bin' => null,
        ];
    }

    public static function prepareAuthorizationParams($params) {

        return [
            'amount' => $params['amount'],
            'fort_id' => $params['fort_id'],
            'merchant_reference' => $params['merchant_reference'],
        ];
    }

    public static function deleteCard($inputs) {
        $validation = Validator::make($inputs, PaymentRequestValidationHelper::deleteCardRules()['rules'], PaymentRequestValidationHelper::deleteCardRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
//        $customer_id = CommonHelper::getRecordByUuid('customers', 'customer_uuid', $inputs['logged_in_uuid']);
        $delete_card = \App\CustomerCard::updateData('customer_card_uuid', $inputs['customer_card_uuid'], ['is_archive' => 1]);
        if (!$delete_card) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse("error occurred while deleting card ");
        }
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request']);
    }

}

?>