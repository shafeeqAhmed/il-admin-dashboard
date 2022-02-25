<?php

namespace App\Helpers\AfterPayment\Transition;

use App\ApplicationCharges;
use App\CurrencyConversion;
use App\Customer;
use App\CustomerCard;
use App\Freelancer;
use App\Helpers\AfterPayment\Transition\Interfaces\TransitionInterface;
use App\Helpers\CommonHelper;
use App\payment\Wallet\Repository\WalletRepo;
use App\Purchases;
use App\PurchasesTransition;
use App\Wallet;
use phpDocumentor\Reflection\Types\Self_;

class Transition implements TransitionInterface {

    public static function insertDataIntoTables($params, $paymentResponse, $appointment, $paramsFor) {
        $purchaseTransition = '';
        $commonData = '';
//        if ($paramsFor == 'class') {
//            $commonData = self::getRawDataForClass($params, $paymentResponse, $appointment);
//        }
        if ($paramsFor == 'appointment') {
            $commonData = self::getRawData($params, $paymentResponse, $appointment);
        }
//        if ($paramsFor == 'package') {
//            $commonData = self::getRawDataForPackage($params, $paymentResponse, $appointment);
//        }
        $commonData['freelancer_id'] = $appointment['freelancer_id'];
        $commonData['customer_id'] = $appointment['customer_id'];
        $purchase = Purchases::createPurchase(self::makeParamsArray($commonData));
        \Log::info('purchse');
        \Log::info($purchase);
        if (!empty($purchase)) {
            $purchaseTransition = PurchasesTransition::createPurchase(self::makePurchaseTransition($params, $purchase, $paymentResponse, $commonData));
            \Log::info('purchse transaction');
            \Log::info($purchaseTransition);
            if (!empty($purchaseTransition)) {
                return ['res' => true, 'purchase_transition' => $purchaseTransition];
            }
        }
        return ['res' => false];
    }

    public static function makeWalletParams($params, $purchase, $paymentResponse, $commonData) {
        return [
            'customer_id' => $params['customer']['id'],
            'amount' => [''],
            'purchase_id' => [''],
            'type' => [''],
            'is_refunded' => [''],
            'customer_card_id' => [''],
            'checkout_transaction_reference' => [''],
        ];
    }

    public static function makeParamsArray($params) {

        return [
            'purchases_short_id' => $params['purchases_short_id'],
            'customer_id' => $params['customer_id'],
            'freelancer_id' => $params['freelancer_id'],
//            'freelancer_id' => $params['freelancer']['id'],
            'purchase_datetime' => $params['data_time'],
            'type' => 'appointment',
            'purchased_by' => $params['purchased_by'],
            'purchased_in_currency' => $params['purchased_in_currency'],
            'service_provider_currency' => $params['service_provider_currency'],
            'conversion_rate' => $params['conversion_rate'],
            'appointment_id' => $params['appointment_id'],
            'customer_card_id' => $params['customer_card_id'],
            'boatek_fee' => $params['boatek_fee'],
            'transaction_charges' => $params['transaction_charges'],
            'service_amount' => $params['service_amount'],
            'total_amount' => $params['total_amount'],
            'discount' => $params['discount'],
            'discount_type' => $params['discount_type'],
            'total_amount_percentage' => $params['total_amount_percentage'],
            'tax' => $params['tax'],
            'boatek_fee_percenatge' => $params['boatek_fee_percenatge'],
            'is_refund' => $params['is_refund'],
            'status' => $params['status'],
        ];
    }

    public static function makePurchaseTransition($params, $purchase, $paymentResponse, $commonData) {
        return [
            'purchase_id' => $purchase['id'],
            'currency' => 'SAR',
//            'currency' => (isset($paymentResponse->currency)) ? $paymentResponse->currency : $params['currency'],
            'amount' => $commonData['total_amount'],
            'transaction_type' => $commonData['transaction_type'],
            'gateway_response' => null,
//            'gateway_response' => serialize($paymentResponse),
            'request_parameters' => serialize($params),
            'transaction_status' => (isset($params['status'])) ? $params['status'] : null,
//            'checkout_transaction_id' => (isset($paymentResponse->id)) ? $paymentResponse->id : null,
            'checkout_transaction_id' => null,
            'appointment_booking_id' => $commonData['appointment_id'],
            'customer_card_id' => null,
//            'customer_card_id' => $commonData['customer_card_id'],
        ];
    }

    public static function globalRecods($params, $paymentResponse, $appointment) {
        $customer = Customer:: getSingleCustomer('customer_uuid', $params['customer_uuid']);
        $freelancerId = (isset($params['freelancer_uuid'])) ? $params['freelancer_uuid'] : CommonHelper::getFreelancerUuidByid($appointment['freelancer_id']);
        $freelancer = Freelancer:: getFreelancerDetail('freelancer_uuid', $freelancerId);
        return [
            'customer' => Customer:: getSingleCustomer('customer_uuid', $params['customer_uuid']),
            'freelancerId' => $freelancerId,
            'freelancer' => $freelancer,
//        'rate' => CurrencyConversion::getCurrency($customer['default_currency'],$freelancer['default_currency']),
        ];
    }

    public static function getRawData($params, $paymentResponse, $appointment) {
        $globalRecords = self::globalRecods($params, $paymentResponse, $appointment);

        return [
            'purchases_short_id' => CommonHelper::shortUniqueId(),
            'purchased_by' => 'card',
            'purchased_in_currency' => 'SAR',
//            'purchased_in_currency' => $globalRecords['customer']['default_currency'],
//            'service_provider_currency' => $globalRecords['freelancer']['default_currency'],
            'service_provider_currency' => 'SAR',
            'status' => (isset($params['status'])) ? $params['status'] : null,
//            'customer_card_id' => ($params['card_id'] != 'wallet') ? CustomerCard::getCustomerCard($globalRecords['customer']['id'], $params['card_id']) : null,
            'customer_card_id' => null,
            'checkout_transaction_reference' => (isset($paymentResponse->id)) ? $paymentResponse->id : null,
            'customer' => $globalRecords['customer'],
            'freelancer' => $globalRecords['freelancer'],
            'data_time' => (date('Y-m-d H:i:s')),
//            'data_time' => strtotime(date('Y-m-d H:i:s')),
            // conversion_rate will be 1 because SAR to SAR payment
            'conversion_rate' => 1,
//            'conversion_rate' => ($globalRecords['rate'] != null) ? $globalRecords['rate']->rate : null,
            'appointment_id' => $appointment['id'],
            'boatek_fee' => CommonHelper::getBoatekFee(),
            'transaction_charges' => 0,
            'service_amount' => null,
//            'service_amount' => $globalRecords['freelancer']['freelancer_categories'][0]['price'],
            'total_amount' => $appointment['paid_amount'],
            'discount' => (isset($params['discount_amount'])) ? $params['discount_amount'] : null,
            'discount_type' => null,
            'total_amount_percentage' => (isset($params['discount_amount'])) ? $params['discount_amount'] : null,
            'tax' => null,
            'boatek_fee_percenatge' => CommonHelper::getBoatekFee(),
            'is_refund' => null,
            'transaction_type' => 'appointment_bookoing'
        ];
    }

    public static function getRawDataForClass($params, $paymentResponse, $appointment) {
        $globalRecords = self::globalRecods($params, $paymentResponse, $appointment);

        return [
            'customer' => $globalRecords['customer'],
            'freelancer' => $globalRecords['freelancer'],
            'data_time' => strtotime(date('Y-m-d H:i:s')),
            'purchased_by' => ($params['card_id'] != 'wallet') ? 'card' : 'wallet',
            'purchased_in_currency' => $globalRecords['customer']['default_currency'],
            'service_provider_currency' => $globalRecords['freelancer']['default_currency'],
            'status' => (isset($params['status'])) ? $params['status'] : null,
            'customer_card_id' => ($params['card_id'] != 'wallet') ? CustomerCard::getCustomerCard($globalRecords['customer']['id'], $params['card_id']) : null,
            'checkout_transaction_reference' => (isset($paymentResponse->id)) ? $paymentResponse->id : null,
            'conversion_rate' => ($globalRecords['rate'] != null) ? $globalRecords['rate']->rate : null,
            'appointment_id' => null,
            'class_booking_id' => $appointment['id'],
            'purchased_package_id' => null,
            'subscription_id' => null,
            'circl_fee' => 2,
            'transaction_charges' => 0,
            'service_amount' => $globalRecords['freelancer']['freelancer_categories'][0]['price'],
            'total_amount' => $appointment['paid_amount'],
            'discount' => $appointment['discount_amount'],
            'discount_type' => null,
            'total_amount_percentage' => $appointment['discount_amount'],
            'tax' => null,
            'circl_fee_percenatge' => ApplicationCharges::where('id', 1)->first()->percentage,
            'is_refund' => null,
            'transaction_type' => 'class_booking'
        ];
    }

    public static function getRawDataForPackage($params, $paymentResponse, $appointment) {

        $globalRecords = self::globalRecods($params, $paymentResponse, $appointment);
        $packageId = CommonHelper::getPackageIdByUuid($params['package_uuid']);
        return [
            'purchased_by' => ($params['card_id'] != 'wallet') ? 'card' : 'wallet',
            'purchased_in_currency' => $globalRecords['customer']['default_currency'],
            'service_provider_currency' => $globalRecords['freelancer']['default_currency'],
            'status' => (isset($params['status'])) ? $params['status'] : null,
            'customer_card_id' => ($params['card_id'] != 'wallet') ? CustomerCard::getCustomerCard($globalRecords['customer']['id'], $params['card_id']) : null,
            'checkout_transaction_reference' => (isset($paymentResponse->id)) ? $paymentResponse->id : null,
            'customer' => $globalRecords['customer'],
            'freelancer' => $globalRecords['freelancer'],
            'data_time' => strtotime(date('Y-m-d H:i:s')),
            'conversion_rate' => ($globalRecords['rate'] != null) ? $globalRecords['rate']->rate : null,
            'appointment_id' => null,
            'class_booking_id' => null,
            'purchased_package_id' => $packageId,
            'subscription_id' => null,
            'circl_fee' => 2,
            'transaction_charges' => 0,
            'service_amount' => $globalRecords['freelancer']['freelancer_categories'][0]['price'],
            'total_amount' => $params['paid_amount'],
            'discount' => $params['discount_amount'],
            'discount_type' => null,
            'total_amount_percentage' => $params['discount_amount'],
            'tax' => null,
            'circl_fee_percenatge' => ApplicationCharges::where('id', 1)->first()->percentage,
            'is_refund' => null,
            'transaction_type' => 'package'
        ];
    }

    public static function updatePurchaseTransition($paymentResponse) {

        return [
            'gateway_response' => serialize($paymentResponse),
            'transaction_status' => (isset($paymentResponse->status)) ? $paymentResponse->status : null,
            'checkout_transaction_id' => (isset($paymentResponse->id)) ? $paymentResponse->id : null,
        ];
    }

}
