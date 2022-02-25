<?php

namespace App\Helpers;

Class PaymentRequestValidationHelper {
    /*
      |--------------------------------------------------------------------------
      | PaymentRequestValidationHelper that contains all the payment request Validation methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use payment request processes
      |
     */

    public static function preparePaymentRequestRules() {
        $validate['rules'] = [
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'currency' => 'required',
            'logged_in_uuid' => 'required'
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function prepareAuthorizationRequestRules() {
        $validate['rules'] = [
            'remember_me' => 'required',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'fort_id' => 'required',
            'merchant_reference' => 'required',
            'logged_in_uuid' => 'required'
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function getCardListRules() {
        $validate['rules'] = [
            'logged_in_uuid' => 'required',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function deleteCardRules() {
        $validate['rules'] = [
            'logged_in_uuid' => 'required',
            'customer_card_uuid' => 'required',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function authorizeCardRules() {
        $validate['rules'] = [
            'logged_in_uuid' => 'required',
            'customer_card_uuid' => 'required'
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function englishMessages() {
        return [
            'remember_me.required' => 'Save my card data is missing',
            'amount.required' => 'Amount is missing',
            'amount.regex' => 'Amount is not in format',
            'successful_request' => 'Request successful!',
            'add_payment_request_error' => 'Payment Request could not be added',
            'bank_detail_error' => 'Bank Details not available , Please add first',
            'fort_id' => 'Fort id is required',
            'merchant_reference' => 'merchant reference is required',
            'customer_card_uuid' => 'customer card uuid is required',
            'logged_in_uuid' => 'logged in uuid is required',
        ];
    }

    public static function arabicMessages() {
        return [
            'remember_me.required' => 'Save my card data is missing',
            'amount.required' => 'Amount is missing',
            'amount.regex' => 'Amount is not in format',
            'successful_request' => 'Request successful!',
            'add_payment_request_error' => 'Payment Request could not be added',
            'bank_detail_error' => 'Bank Details not available , Please add first',
            'fort_id' => 'Fort id is required',
            'merchant_reference' => 'merchant reference is required',
        ];
    }

}

?>
