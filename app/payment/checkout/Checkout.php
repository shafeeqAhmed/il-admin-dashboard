<?php

namespace App\payment\checkout;

use App\Customer;
use App\CustomerCard;
use App\Helpers\AfterPayment\Transition\Transition;
use App\Helpers\AppointmentValidationHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ExceptionHelper;
use App\Helpers\MessageHelper;
use \App\payment\checkout\Interfaces\CheckoutInterface;
use App\payment\Wallet\Repository\WalletRepo;
use App\Traits\Checkout\PaymentHelper;
use App\Wallet;
use Checkout\CheckoutApi;
use Checkout\Models\Payments\TokenSource;
use Checkout\tests\CheckoutApiTest;
use Checkout\tests\Models\Payments\TokenSourceTest;
use Checkout\Models\Payments\Payment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;

class Checkout implements CheckoutInterface {

    use PaymentHelper;

    private $key = '';
    protected $token = '';

    public static function paymentType($inputs, $save_appointment, $paramsFor) {
        if ($inputs['paid_amount'] <= 0) {
            return ['res' => true];
        }
        $paymentResult = '';
        $inputs['status'] = 'pending';
        $records = self::entryInPurchases($inputs, $paymentResult, $save_appointment, $paramsFor);
        $inputs['purchase_transition'] = $records;
//        $paymentResult = Checkout::processPayment($inputs, 'payments');
//        if ((isset($paymentResult->status)) && $paymentResult->status == 'Pending') {
        if ($records['res'] == false) {
            return ['res' => false, 'message' => 'error occurred while saving purchase'];
        }
//            return ['res' => 'verify', 'link' => $paymentResult->_links->redirect->href, 'purchase_transition_id' => $records['purchase_transition']['id']];
//        }
//        if ($paymentResult == 422) {
//            DB::rollBack();
//            return ['res' => false, 'message' => CommonHelper::jsonErrorResponse('Your Card is Expired')];
//        }
//            DB::commit();

        return ['res' => 'verify'];
    }

    public static function entryInPurchases($inputs, $paymentResult, $save_appointment, $paramsFor) {
        return Transition::insertDataIntoTables($inputs, $paymentResult, $save_appointment, $paramsFor);
    }

    public static function getPaymentDetail($ckoId, $slug) {

        $response = (new Client())->get(env('CHECKOUT_BASE_URL') . $slug . '/' . $ckoId,
                [
                    'headers' => [
                        'Authorization' => env('CHECKOUT_KEY'), 'Content-Type' => 'application/json;charset=UTF-8'
                    ]
                ]
        );

        return json_decode($response->getBody()->getContents());
    }

    public static function processPayment($params, $slug) {
        return self::makeGhuzzleRequest(self::paymentParams($slug, $params), $slug, 'post');
    }

    // TODO: Implement makePaymentRequest() method.
    public static function makeGhuzzleRequest($params, $slug, $requestType = 'post') {
        try {
            $client = new Client();
            $response = $client->$requestType(env('CHECKOUT_BASE_URL') . $slug, [
                'json' => $params,
                'headers' => [
                    'Authorization' => env('CHECKOUT_KEY'),
                    'Content-Type' => 'application/json;charset=UTF-8']
            ]);

            return json_decode($response->getBody()->getContents());
        } catch (\Exception $ex) {

            return $ex->getCode();
        }
    }

    public static function getToken() {
        $params = [
            'type' => 'card',
            'number' => '4658584090000001',
            'expiry_month' => '04',
            'expiry_year' => '2025',
            'cvv' => '257',
            'name:waqas'
        ];

        $client = new Client();

        $response = $client->post(env('CHECKOUT_BASE_URL') . 'tokens', [
            'json' => $params,
            'headers' => [
                'Authorization' => 'pk_test_982d73c4-5c47-4381-89d1-090b25785ce6',
                'Content-Type' => 'application/json;charset=UTF-8']
        ]);

        dd(json_decode($response->getBody()->getContents()));
    }

    public static function deleteUserCard($inputs) {
        $validation = Validator::make($inputs, AppointmentValidationHelper::deleteCardRules()['rules'], AppointmentValidationHelper::TorkenRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return ['msg' => 'false', 'error' => $validation->errors()->first()];
        }
        $customerId = CommonHelper::getCutomerIdByUuid($inputs['customer_uuid']);

        $response = CustomerCard::where('customer_id', $customerId)->where('card_id', $inputs['card_id'])->update(['is_archive' => 1]);

        if ($response) {
            return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['delete_card_success']);
        } else {
            return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['delete_card_error']);
        }
    }

    public static function addCustomerToken($inputs) {
        $validation = Validator::make($inputs, AppointmentValidationHelper::TorkenRules()['rules'], AppointmentValidationHelper::TorkenRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return ['msg' => 'false', 'error' => $validation->errors()->first()];
        }
        $params = Checkout::paymentParams('instruments', $inputs);

        $cardDetail = self::makeGhuzzleRequest($params, 'instruments');

        $inputs['customer_id'] = CommonHelper::getCutomerIdByUuid($inputs['customer_uuid']);

        $tableParams = self::makeCustomerCardParams($inputs, $cardDetail);

        if (!CustomerCard::checkCardEntry($inputs['customer_id'], $cardDetail->id)) {
            $response = self::makeCardResponse(CustomerCard::create($tableParams), $inputs['customer_id']);
        } else {
            return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['card_already_inserter']);
        }
        if ($response) {
            return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
        } else {
            return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_card_error']);
        }
    }

    public static function getCardDetail($inputs) {
        $inputs['customer_id'] = CommonHelper::getCutomerIdByUuid($inputs['customer_uuid']);
        $cards = CustomerCard::where('customer_id', $inputs['customer_id'])->where('is_archive', 0)->get()->toArray();
        $response = [
            'balance' => (double) number_format(Wallet::getWalletTotalAmount($inputs), 2),
            'cards' => self::getCustomerCardsDetail($cards, $inputs)
        ];
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function getCustomerCardsDetail($cards, $inputs) {
        $response = [];
        foreach ($cards as $card) {
            $response[] = self::makeCardResponse($card, $inputs['customer_id']);
        }
        return $response;
    }

    public static function makeCardResponse($card, $customerUUId) {
        return [
            'card_id' => $card['card_id'],
            'customer_uuid' => $customerUUId,
            'type' => strtolower($card['card_name']),
            'last_digits' => $card['last_digits'],
            'expiry' => $card['expiry'],
        ];
    }

    public static function makeCustomerCardParams($params, $cardDetail) {

        return [
            'customer_id' => $params['customer_id'],
            'card_id' => $cardDetail->id,
            'token' => $params['token'],
            'card_name' => (isset($cardDetail->scheme)) ? $cardDetail->scheme : null,
            'type' => (isset($cardDetail->card_type)) ? $cardDetail->card_type : null,
            'last_digits' => (isset($cardDetail->last4)) ? $cardDetail->last4 : null,
            'expiry' => $cardDetail->expiry_month . '-' . $cardDetail->expiry_year,
            'customer_checkout_id' => (isset($cardDetail->customer->id)) ? $cardDetail->customer->id : null,
            'bin' => (isset($cardDetail->bin)) ? $cardDetail->bin : null,
        ];
    }

    public static function capturePayment($inputs) {
        // payfort
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $url = config('general.payfort.payfort_url');

        $requestParams = array(
            'command' => 'CAPTURE',
            'access_code' => config('general.payfort.access_code'),
            'merchant_identifier' => config('general.payfort.merchant_identifier'),
            'merchant_reference' => $inputs['merchant_reference'],
            'amount' => $inputs['amount'],
            'currency' => 'SAR',
            'language' => 'en',
            'fort_id' => $inputs['fort_id'],
//            'order_description' => 'iPhone 6-S',
        );
        $signature = \App\Helpers\PaymentRequestHelper::prepareSignature($requestParams);
        $requestParams['signature'] = $signature;
        $decoded_response = \App\Helpers\PaymentRequestHelper::prepareCurlRequest($url, $requestParams);
        return $decoded_response;
        // checkout method
//        $slug = 'payments/'.$inputs['payment_id'].'/captures';
//        $response = (new Client())->post(env('CHECKOUT_BASE_URL').$slug,['headers'=>['Authorization' => env('CHECKOUT_KEY'), 'Content-Type' => 'application/json;charset=UTF-8']]);
//        json_decode($response->getBody()->getContents());
//        return self::getPaymentDetail($inputs['payment_id'], 'payments');
    }

    public static function topUp($inputs) {

        $wallet = Wallet::create(WalletRepo::topUpInWallet($inputs));
        $inputs['wallet_id'] = $wallet->id;
        $bankConformation = self::makeGhuzzleRequest(PaymentHelper::paymentParams('topUp', $inputs), 'payments', 'post');

        if ($bankConformation->status == 'Pending') {
            return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_appointment_success'],
                            [
                                'res' => 'verify',
                                'link' => $bankConformation->_links->redirect->href,
                                'wallet_id' => $wallet->id
                            ]
            );
        }
        return ['res' => false];
    }

}
