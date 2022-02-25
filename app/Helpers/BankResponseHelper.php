<?php

namespace App\Helpers;

Class BankResponseHelper {
    /*
      |--------------------------------------------------------------------------
      | BankResponseHelper that contains all the exception related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper Bank related stuff
      |
     */

    public static function setResponse($bank_detail) {

        $response = [];
        if (!empty($bank_detail)) {
            $response['bank_detail_uuid'] = $bank_detail['bank_detail_uuid'];
            $response['user_uuid'] = CommonHelper::getRecordByUuid('users', 'id', $bank_detail['user_id'], 'user_uuid');
            $response['account_name'] = $bank_detail['account_name'];
            $response['account_title'] = $bank_detail['account_title'];
            $response['iban_account_number'] = $bank_detail['location_type'] == 'KSA' ? $bank_detail['iban_account_number'] : $bank_detail['account_number'];
            $response['bank_name'] = !empty($bank_detail['bank_name']) ? $bank_detail['bank_name'] : null;
            $response['sort_code'] = !empty($bank_detail['sort_code']) ? $bank_detail['sort_code'] : null;
            $response['billing_address'] = !empty($bank_detail['billing_address']) ? $bank_detail['billing_address'] : null;
            $response['post_code'] = !empty($bank_detail['post_code']) ? $bank_detail['post_code'] : null;
            $response['location_type'] = $bank_detail['location_type'];
        }
        return $response;
    }

    public static function setTransactionTitle($data = []) {
        $title = null;
        if (!empty($data['appointment'])) {
            $title = $data['appointment']['title'];
        } elseif (!empty($data['class_book']['class_object'])) {
            $title = $data['class_book']['class_object']['name'];
        } elseif (!empty($data['subscription'])) {
            $title = ucfirst($data['subscription']['subscription_setting']['type']) . ' subscription';
        }
        return $title;
    }

    public static function setTransactionResposne($dataArray = [], $inputs) {

        $timezone = $inputs['local_timezone'] ?? 'UTC';
        $login_user_type = $inputs['login_user_type'];
        $type = $inputs['type'];
        $response = [];
        if (!empty($dataArray)) {
            $total_count = 0;
            $index = 0;
            foreach ($dataArray as $key => $value) {
//                if ($login_user_type == "freelancer"  && isset($value['payment_due']) && count($value['payment_due']) > 0) {
//                if ($login_user_type == "freelancer" ) {
//                    foreach ($value['payment_due'] as $due_payment) {
//                        $diff = self::checkDateTimeDifference($value, $due_payment, $type);
//                        if ($diff) {
//                            $response[] = self::setTransactionListCommonResponse($value, $timezone, $login_user_type, $due_payment, $type);
//                        }
//                    }
//                } else {
//                    $diff = self::checkDateTimeDifference($value, [], $type);
//                    if ($diff) {
//                        $response[] = self::setTransactionListCommonResponse($value, $timezone, $login_user_type, [], $type);
//                    }
//                }
                $diff = self::checkDateTimeDifference($value, $type);
                if ($diff) {
                    $response[] = self::setTransactionListCommonResponse($value, $timezone, $login_user_type, [], $type);
                }
            }
        }
        return $response;
    }

    public static function checkDateTimeDifference($data, $list_type) {

        if ($list_type == "all") {
            return true;
        }
        $appointment_time = $data['appointment_date'] . ' ' . $data['from_time'];

        $diff = CommonHelper::checkDateTimeDifferenceInMinutes($appointment_time);

        if ($list_type == "available" && isset($diff)) {
            if ($diff >= 1440) {
                return true;
            }
        }
        if ($list_type == "pending" && isset($diff)) {
            if ($diff < 1440) {
                return true;
            }
        }

        return false;
    }

    public static function setTransactionListCommonResponse($value, $timezone, $login_user_type, $due_payment = [], $list_type) {
//        $response['name'] = self::setTransactionTitle($value);
        $response['name'] = $value['title'];
//        $response['uuid'] = $value['freelancer_transaction_uuid'];
        $response['uuid'] = !empty($value['purchase']) ? $value['purchase']['purchases_uuid'] : null;
//        $response['freelancer_uuid'] = $value['freelancer_uuid'];
        $response['freelancer_uuid'] = !empty($value['appointment_freelancer']) ? $value['appointment_freelancer']['freelancer_uuid'] : null;
        $response['payment_due_uuid'] = isset($due_payment['payment_due_uuid']) ? $due_payment['payment_due_uuid'] : null;
        $response['freelancer_name'] = !empty($value['appointment_freelancer']) ? $value['appointment_freelancer']['first_name'] . ' ' . $value['appointment_freelancer']['last_name'] : null;
        $response['freelancer_profile_image'] = !empty($value['appointment_freelancer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $value['appointment_freelancer']['profile_image'] : null;
//        $response['type'] = self::setTransactionType($value);
        $response['type'] = 'appointment_booking';
        $response['amount'] = self::getTransactionAndPaymentDueAmountForList($value, $login_user_type, $due_payment);
//        $response['transaction_id'] = !empty($value['transaction_id']) ? $value['transaction_id'] : null;
        $response['transaction_id'] = !empty($value['purchase']) ? $value['purchase']['purchases_short_id'] : null;
        $response['travel_fee'] = self::calculateTravelFee($value);
        $response['date'] = !empty($value['purchase']) ? date('d M Y', strtotime($value['purchase']['purchase_datetime'])) : null;

        $time = !empty($value['purchase']) ? date('H:i:s', strtotime($value['purchase']['purchase_datetime'])) : null;
        $response['time'] = CommonHelper::convertTimeToTimezone($time, 'UTC', $timezone);

        $response['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
        $response['customer_uuid'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['customer_uuid'] : null;
        $response['profile_image'] = (!empty($value['appointment_customer']) && !empty($value['appointment_customer']['user'])) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_customer']['user']['profile_image'] : null;
        $response['payment_by'] = 'card';

//        $response['transaction_status'] = self::prepareTransactionStatus($value);
        $response['transaction_status'] = !empty($value['purchase']) ? $value['purchase']['status'] : null;
        ;

//        $response['currency'] = null;
//        if ($login_user_type == "freelancer") {
//            $response['currency'] = $value['freelancer']['default_currency'] ?? '';
//        } elseif ($login_user_type == "customer") {
//            $response['currency'] = $value['to_currency'];
//        }
        $response['currency'] = null;
        if ($login_user_type == "freelancer") {
            $response['currency'] = !empty($value['purchase']) ? $value['purchase']['service_provider_currency'] : null;
        } elseif ($login_user_type == "customer") {
            $response['currency'] = !empty($value['purchase']) ? $value['purchase']['purchased_in_currency'] : null;
            ;
        }
//        $response['location'] = self::setTransactionLocation($value);
        $response['location'] = $value['address'];

        $response['session_number'] = isset($due_payment['due_no']) ? $due_payment['due_no'] : 1;
        $response['total_session'] = isset($due_payment['total_dues']) ? $due_payment['total_dues'] : 1;
        $response['is_package'] = false;
        $response['session_status'] = null;

        if (!empty($value['appointment'])) {
            $response['session_status'] = $value['appointment']['status'];
        } elseif (!empty($value['class_book'])) {
            $response['session_status'] = $value['class_book']['status'];
        }
        if (!empty($value['appointment']['package_uuid'])) {
            $response['session_number'] = $value['appointment']['session_number'];
            $response['total_session'] = $value['appointment']['total_session'];
            $response['is_package'] = true;
        } elseif (!empty($value['class_book']['package_uuid'])) {
            $response['session_number'] = $value['class_book']['session_number'];
            $response['total_session'] = $value['class_book']['total_session'];
            $response['is_package'] = true;
        }
        if ($value['status'] == 'cancelled') {
            $response['is_refunded'] = isset($value['refund_transaction']['id']) ? 1 : 0;
            $response['refund_transaction_uuid'] = $value['refund_transaction']['refund_transaction_uuid'] ?? '';
            $response['refund_type'] = $value['refund_transaction']['refund_type'] ?? '';
            $response['refund_amount'] = $value['refund_transaction']['amount'] ?? 0;
            $response['refund_currency'] = $value['refund_transaction']['currency'] ?? '';

            //this comment section will be updated after refund module
//            $response['refund_amount'] = self::convertAmountToFreelancerCurrency($value['freelancer'], $login_user_type, $response['refund_amount'], $value['exchange_rate'], $value['to_currency']);
//            $response['amount'] = $response['amount'] - $response['refund_amount'];
//            $response['amount'] = $response['amount'] < 0 ? 0 : $response['amount'];

            $response['refund_amount'] = null;
            $response['amount'] = $response['amount'] - $response['refund_amount'];
            $response['amount'] = $response['amount'] < 0 ? 0 : $response['amount'];
        }

        return $response;
    }

    public function transactionDate($appointment) {
        $date = null;
        if (!empty($appointment)) {
            $from = CommonHelper::convertTimeZoneToDate($appointment['appointment_start_date_time'], $appointment['saved_timezone'], $appointment['local_timezone']);
            $to = CommonHelper::convertTimeZoneToDate($appointment['appointment_end_date_time'], $appointment['saved_timezone'], $appointment['local_timezone']);
            $date = $from . ' - ' . $to;
        }
        return $date;
    }

    public function transactionTime($appointment) {
        $date = null;
        if (!empty($appointment)) {
            $from = CommonHelper::convertTimeZoneToTime($appointment['appointment_start_date_time'], $appointment['saved_timezone'], $appointment['local_timezone']);
            $to = CommonHelper::convertTimeZoneToTime($appointment['appointment_end_date_time'], $appointment['saved_timezone'], $appointment['local_timezone']);
            $date = $from . ' - ' . $to;
        }
        return $date;
    }

    public static function getTransactionAndPaymentDueAmountForList($value, $login_user_type, $payment_due = []) {
//        if (isset($payment_due['payment_due_uuid'])) {
//            if (isset($value['appointment_freelancer']['default_currency']) && strtolower($value['appointment_freelancer']['default_currency']) == 'pound') {
//                $amount = $payment_due['pound_amount'];
//            }
//            if (isset($value['freelancer']['default_currency']) && strtolower($value['freelancer']['default_currency']) == 'sar') {
//                $amount = $payment_due['sar_amount'];
//            }
//        } else {
//            if (!empty($login_user_type) && $login_user_type == 'customer') {
//                $amount = $value['total_amount'];
//            } else {
//                $amount = $value['total_amount'] - ($value['boatek_fee']);
//                $amount = self::convertAmountToFreelancerCurrency($value['freelancer'], $login_user_type, $amount, $value['exchange_rate'], $value['to_currency']);
//            }
//        }
        $amount = 0;
        if (!empty($login_user_type) && $login_user_type == 'customer') {
            $amount = !empty($value['purchase']) ? $value['purchase']['total_amount'] : 0;
        } else {
            $amount = !empty($value['purchase']) ? $value['purchase']['total_amount'] - ($value['purchase']['boatek_fee']) : 0;
            //there is not conversion in currency
//            $amount = self::convertAmountToFreelancerCurrency($value['appointment_freelancer'], $login_user_type, $amount, $value['exchange_rate'], $value['to_currency']);
        }

        return round($amount, 2);
    }

    public static function convertAmountToFreelancerCurrency($freelancer, $login_user_type, $amount, $exchangeRate, $convertedCurr) {
        if (!empty($login_user_type) && $login_user_type == 'customer') {
            $amount = $amount;
        } else {

            if (isset($freelancer['default_currency']) && strtolower($freelancer['default_currency']) == strtolower($convertedCurr)) {
                $amount = $amount;
            } else {
                if (!empty($exchangeRate) && $exchangeRate >= 0) {
                    $amount = $amount / $exchangeRate;
                }
                $amount = round($amount, 2);
            }
        }
        return $amount;
    }

    public static function prepareTransactionStatus($data) {
        $status = null;
//        if (!empty($data['appointment'])) {
//            if ($data['appointment']['status'] == "pending" || $data['appointment']['status'] == "confirmed") {
//                $status = "pending";
//            } elseif ($data['appointment']['status'] == "completed") {
//                $status = "completed";
//            } elseif ($data['appointment']['status'] == "cancelled") {
//                $status = "cancelled";
//            } elseif ($data['appointment']['status'] == "rejected") {
//                $status = "rejected";
//            }
//        } elseif (!empty($data['class_book'])) {
//            if ($data['class_book']['status'] == "pending" || $data['class_book']['status'] == "confirmed") {
//                $status = "pending";
//            } elseif ($data['class_book']['status'] == "completed") {
//                $status = "completed";
//            } elseif ($data['class_book']['status'] == "cancelled") {
//                $status = "cancelled";
//            } elseif ($data['class_book']['status'] == "rejected") {
//                $status = "rejected";
//            }
//        }
        if (!empty($data)) {
            if ($data['status'] == "pending" || $data['status'] == "confirmed") {
                $status = "pending";
            } elseif ($data['status'] == "completed") {
                $status = "completed";
            } elseif ($data['status'] == "cancelled") {
                $status = "cancelled";
            } elseif ($data['status'] == "rejected") {
                $status = "rejected";
            }
        }
        return $status;
    }

    public static function getTotalAppointmentCount($data) {
        $count_array = [];
        if (!empty($data)) {
            $appointment_data = \App\Appointment::getAppointmentWithPurchasedPackages('purchased_package_uuid', $value['appointment']['purchased_package_uuid']);
            $count_array['total_count'] = count($appointment_data);
            foreach ($appointment_data as $key => $appointment) {
                if ($appointment['purchased_package_uuid'] == $data['purchased_package_uuid']) {
                    
                }
            }
        }
    }

//    public static function setAppointmentsResponse($appointments = [], $response) {
//        if (!empty($appointments)) {
//            foreach ($appointments as $appointment) {
//                $key = count($response) + 1;
//                $response[$key] = self::setAppointResponse($appointment);
//            }
//        }
//        return $response;
//    }

    public static function setTransactionDetailResponse($data, $inputs) {
        $timezone = $inputs['local_timezone'];
        $response = [];
        if (!empty($data)) {
            if (isset($inputs['payment_due_uuid']) && !empty($inputs['payment_due_uuid']) && isset($data['payment_due']) && count($data['payment_due']) > 0) {
                $due_key = array_search($inputs['payment_due_uuid'], array_column($data['payment_due'], 'payment_due_uuid'));
                $data['single_pay_due'] = $data['payment_due'][$due_key];
            }
            $response['uuid'] = $data['purchases_uuid'];
            $response['freelancer_uuid'] = $data['freelancer']['freelancer_uuid'];
            $response['boat_owner_name'] = ((!empty($data['freelancer'])) && (!empty($data['freelancer']['user']))) ? $data['freelancer']['user']['first_name'] . ' ' . $data['freelancer']['user']['last_name'] : null;
            $response['freelancer_name'] = !empty($data['freelancer']) ? $data['freelancer']['first_name'] . ' ' . $data['freelancer']['last_name'] : null;
            $response['freelancer_profile_image'] = !empty($data['freelancer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['freelancer']['profile_image'] : null;
//            $response['purchase_type'] = self::setTransactionType($data);
            $response['purchase_type'] = $data['type'];
//            $response['purchase_detail'] = self::getPurchaseDetails($data);
            $response['purchase_detail'] = 'Single Appointment';
//            $response['booking_name'] = self::setTransactionTitle($data);
            $response['booking_name'] = $data['appointment']['title'];

            $response['transaction_id'] = $data['purchases_short_id'];
            $response['travel_fee'] = null;
            $response['travel_fee'] = self::calculateTravelFee($data);
            $response['purchase_date'] = date('d M Y', strtotime($data['purchase_datetime']));
            $time = date('H:i:s', strtotime($data['purchase_datetime']));
            $response['time'] = CommonHelper::convertTimeToTimezone($time, 'UTC', $timezone);
            $response['customer'] = !empty($data['customer']['user']) ? $data['customer']['user']['first_name'] . ' ' . $data['customer']['user']['last_name'] : null;
            $response['customer_uuid'] = !empty($data['customer']) ? $data['customer']['customer_uuid'] : null;
            $response['profile_image'] = !empty($data['customer']['user']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $data['customer']['user']['profile_image'] : null;
            $response['payment_by'] = $data['purchased_by'];
//            $response['transaction_status'] = self::prepareTransactionStatus($data);
            $response['transaction_status'] = $data['status'];
//            $response['circl_charges'] = !empty($data['circl_charges']) ? $data['circl_charges'] : null;
            $response['transaction_charges'] = !empty($data['transaction_charges']) ? $data['transaction_charges'] : null;
            $response['boatek_fee'] = !empty($data['boatek_fee']) ? $data['boatek_fee'] : null;
//            $response['location'] = self::setTransactionLocation($data);
            $response['location'] = $data['appointment']['address'];
            $response['online_link'] = self::setTransactionSessionLink($data);
            $response['type'] = self::checkBookingIsOnline($data) != null ? 'online' : 'face to face';
//            $response['booking_date'] = self::getTransactionBookingDate($data);
//            $response['booking_time'] = self::getTransactionBookingTime($data, $timezone);

            $response['booking_date'] = date('d M Y', $data['appointment']['appointment_start_date_time']);
            $response['booking_end_date'] = date('d M Y', $data['appointment']['appointment_end_date_time']);

            $response['booking_time'] = CommonHelper::convertTimeToTimezone($data['appointment']['from_time'], 'UTC', $timezone);
            $response['booking_end_time'] = CommonHelper::convertTimeToTimezone($data['appointment']['to_time'], 'UTC', $timezone);
            $response['price_per_half_hour'] = $data['appointment']['price_per_half_hour'];
            $response['duration'] = AppointmentResponseHelper::calculateDuration($data['appointment']);
            $response['price'] = $data['appointment']['price'];
            $response['paid_amount'] = $data['appointment']['paid_amount'];
            $response['boat_discount_hours'] = !empty($data['appointment']['boat_discount_hours']) ? $data['appointment']['boat_discount_hours'] : null;
            $response['boat_discount_hours_percentage'] = !empty($data['appointment']['boat_discount_hours_percentage']) ? $data['appointment']['boat_discount_hours_percentage'] : null;

            $exchange_rate = 1;
            $exchange_type = '';
            if ($inputs['login_user_type'] == "freelancer") {
                $response['currency'] = $data['freelancer']['default_currency'] ?? '';
                $response['service_fee'] = !empty($data['total_amount']) ? $data['total_amount'] : 0;
                $response['amount'] = $response['service_fee'] - ($response['boatek_fee']);
                $response['amount'] = round($response['amount'], 2);
                $exchange_rate = !empty($data['exchange_rate']) ? $data['exchange_rate'] : null;
                $promo_discount = self::getPromoDiscount($data, $response);
                $response['promo_discount'] = !empty($promo_discount) ? $promo_discount : 0;
//                $response['discount'] = self::getDiscountPrice($data, $response);
                $response['promo_code'] = self::getPromoCode($data);

//                if (isset($data['freelancer']['default_currency']) && strtolower($data['freelancer']['default_currency']) != strtolower($data['to_currency'])) {
//                    $exchange_type = 'd';
//                }
            }

            if ($inputs['login_user_type'] == "customer") {
                $response['currency'] = 'SAR';
//                $response['currency'] = !empty($data['customer']) ? $data['customer']['user']['currency'] : 'SAR';
                $response['service_fee'] = !empty($data['actual_amount']) ? $data['actual_amount'] : 0;
                $response['amount'] = $data['total_amount'] ?? 0;
                $promo_discount = self::getPromoDiscount($data, $response);
                $response['promo_discount'] = !empty($promo_discount) ? $promo_discount : 0;
            }
            $response['discount'] = self::getDiscountPrice($data, $response);
            $response['promo_code'] = self::getPromoCode($data);

            if (!empty($exchange_type)) {
//                if ($exchange_type == 'd') {
//                    $response['service_fee'] = round($response['service_fee'] / $exchange_rate, 2);
//                    $response['circl_charges'] = round($response['circl_charges'] / $exchange_rate, 2);
//                    $response['hyperpay_fee'] = round($response['hyperpay_fee'] / $exchange_rate, 2);
//                    $response['amount'] = round($response['amount'] / $exchange_rate, 2);
//                    $response['discount'] = round($response['discount'] / $exchange_rate, 2);
//                }
//                if ($exchange_type == 'm') {
//                    $response['service_fee'] = round($response['service_fee'] * $exchange_rate, 2);
//                    $response['circl_charges'] = round($response['circl_charges'] * $exchange_rate, 2);
//                    $response['hyperpay_fee'] = round($response['hyperpay_fee'] * $exchange_rate, 2);
//                    $response['amount'] = round($response['amount'] * $exchange_rate, 2);
//                    $response['discount'] = round($response['discount'] * $exchange_rate, 2);
//                }
            }

//            if ($data['transaction_type'] == 'subscription') {
//                $response['subscription_type'] = $data['subscription']['subscription_setting']['type'];
//            }
//            // get amount from payment_due table if transaction type is subscription
//            if ($data['transaction_type'] == 'subscription' && isset($inputs['payment_due_uuid']) && !empty($inputs['payment_due_uuid']) && isset($data['payment_due']) && count($data['payment_due']) > 0) {
//                $response['due_amount'] = self::getPaymentDueAmountForSingleTransaction($data, $inputs);
//                $response['due_date'] = isset($data['single_pay_due']['due_date']) ? date('d M Y', strtotime($data['single_pay_due']['due_date'])) : null;
//            }

            $session_number_info = self::getSessionNumberDetails($data);
            $response['total_session'] = $session_number_info['total_session'];
            $response['session_number'] = $session_number_info['session_number'];

//            if (!empty($data['appointment'])) {
//                $response['session_status'] = $data['appointment']['status'];
//            } elseif (!empty($data['class_book'])) {
//                $response['session_status'] = $data['class_book']['status'];
//            }
            $response['session_status'] = $data['appointment']['status'];
            //it will be update after refund amount
//            if ($data['status'] == 'cancelled') {
//                $response['is_refunded'] = isset($data['refund_transaction']['id']) ? 1 : 0;
//                $response['refund_transaction_uuid'] = $data['refund_transaction']['refund_transaction_uuid'] ?? '';
//                $response['refund_type'] = $data['refund_transaction']['refund_type'] ?? '';
//                $response['refund_amount'] = $data['refund_transaction']['amount'] ?? 0;
//                $response['refund_currency'] = $data['refund_transaction']['currency'] ?? '';
//
//                $response['amount'] = $response['amount'] - $response['refund_amount'];
//            }
        }
        return $response;
    }

    public static function getPaymentDueAmountForSingleTransaction($data, $inputs) {
        $amount = 0;
        $freelancer_currency = isset($data['freelancer']['default_currency']) ? $data['freelancer']['default_currency'] : '';
        if (isset($data['single_pay_due'])) {
            if (strtolower($freelancer_currency) == 'pound') {
                $amount = $data['single_pay_due']['pound_amount'];
            } else {
                $amount = $data['single_pay_due']['sar_amount'];
            }
        }

        return round($amount, 2);
    }

    public static function getPromoDiscount($data) {

        $code = 0;
        if (!empty($data['appointment']['promo_code'])) {
            $code = $data['appointment']['promo_code']['coupon_amount'] ?? '';
        }


        return $code;
    }

    public static function getPromoCode($data) {

        $code = '';
        if (!empty($data['appointment']['promo_code'])) {
            $code = $data['appointment']['promo_code']['coupon_code'] ?? '';
        }


        return $code;
    }

    public static function getDiscountPrice($data, $response) {

        $discounted_price = 0;
        $discount = 0;
        if (!empty($data)) {
            if ($data['type'] == 'appointment') {
                $discounted_price = $data['appointment']['discounted_price'] ?? 0;
            }

//            if ($data['transaction_type'] == 'class_booking') {
//                $discounted_price = $data['class_book']['discounted_price'] ?? 0;
//            }

            $discount = ($discounted_price > 0) ? $response['service_fee'] - $discounted_price : 0;
            $discount = round(abs($discount), 2);
        }
        return $discount;
    }

    public static function getSessionNumberDetails($data) {

        $resp = [
            'session_number' => 0,
            'total_session' => 0
        ];

        if ($data['type'] == 'appointment') {
            if (!empty($data['appointment']['session_number']) && !empty($data['appointment']['total_session'])) {
                $resp['session_number'] = $data['appointment']['session_number'];
                $resp['total_session'] = $data['appointment']['total_session'];
            }
        }

//        if ($data['transaction_type'] == 'class_booking') {
//            if (!empty($data['class_book']['session_number']) && !empty($data['class_book']['total_session'])) {
//                $resp['session_number'] = $data['class_book']['session_number'];
//                $resp['total_session'] = $data['class_book']['total_session'];
//            }
//        }
//        if ($data['transaction_type'] == 'subscription') {
//            if (isset($data['single_pay_due'])) {
//                $resp['session_number'] = $data['single_pay_due']['due_no'];
//                $resp['total_session'] = $data['single_pay_due']['total_dues'];
//            }
//        }

        return $resp;
    }

    public static function getPurchaseDetails($data) {
        $name = '';
        if ($data['transaction_type'] == 'appointment_bookoing') {
            if (isset($data['appointment']['package']['package_name'])) {
                $name = $data['appointment']['package']['package_name'];
            } else {
                $name = "Single Appointment";
            }
        }

        if ($data['transaction_type'] == 'class_booking') {
            if (isset($data['class_book']['package']['package_name'])) {
                $name = $data['class_book']['package']['package_name'];
            } else {
                $name = "Single Class";
            }
        }

        if ($data['transaction_type'] == 'subscription') {
            $name = 'Subscription';
        }

        return $name;
    }

    public static function getTransactionBookingDate($data = []) {
        if ($data['transaction_type'] == 'appointment_bookoing') {
            return date('d M Y', strtotime($data['appointment']['appointment_date']));
        } elseif ($data['transaction_type'] == 'class_booking') {
            return date('d M Y', strtotime($data['class_book']['schedule']['class_date']));
        } elseif ($data['transaction_type'] == 'subscription') {
            return date('d M Y', strtotime($data['subscription']['subscription_date']));
        }
    }

    public static function getTransactionBookingTime($data = [], $timezone) {
        if ($data['transaction_type'] == 'appointment_bookoing') {
            return CommonHelper::convertTimeToTimezone($data['appointment']['from_time'], 'UTC', $timezone);
        } elseif ($data['transaction_type'] == 'class_booking') {
            return CommonHelper::convertTimeToTimezone($data['class_book']['schedule']['from_time'], 'UTC', $timezone);
        } elseif ($data['transaction_type'] == 'subscription') {
            return '';
        }
    }

    public static function setTransactionType($data = []) {
        if ($data['transaction_type'] == 'appointment_bookoing') {
            if (!empty($data['appointment']['package_uuid'])) {
                return 'Session Package';
            } else {
                return 'Session';
            }
        } elseif ($data['transaction_type'] == 'class_booking') {
            if (!empty($data['class_book']['package_uuid'])) {
                return 'Class Package';
            } else {
                return 'Class';
            }
        } elseif ($data['transaction_type'] == 'subscription') {
            return 'Subscription';
        } elseif ($data['transaction_type'] == 'refund') {
            return 'Refund';
        }
        return $data['transaction_type'];
    }

    public static function checkBookingIsOnline($data = []) {
        $link = null;
        if (isset($data['appointment']['is_online']) && !empty($data['appointment']['is_online'])) {
            $link = $data['appointment']['is_online'];
        } elseif (!empty($data['class_book']) && !empty($data['class_book']['class_object']['online_link'])) {
            $link = $data['class_book']['class_object']['online_link'];
        }
        return $link;
    }

    public static function setTransactionSessionLink($data = []) {
        $link = null;
        if (!empty($data['appointment']) && !empty($data['appointment']['online_link'])) {
            $link = $data['appointment']['online_link'];
        } elseif (!empty($data['class_book']) && !empty($data['class_book']['class_object']['online_link'])) {
            $link = $data['class_book']['class_object']['online_link'];
        }
        return $link;
    }

    public static function setTransactionLocation($data = []) {
        $location = null;
        if (!empty($data['appointment']) && !empty($data['appointment']['address'])) {
            $location = $data['appointment']['address'];
        } elseif (!empty($data['class_book']) && !empty($data['class_book']['class_object']['address'])) {
            $location = $data['class_book']['class_object']['address'];
        }
        return $location;
    }

    public static function calculateTravelFee($data = []) {
        $travel_cost = null;
        if (!empty($data['appointment']) && $data['appointment']['location_type'] != 'freelancer' && $data['freelancer']['can_travel'] == 1) {
            if (!empty($data['appointment']['travelling_distance'])) {
                $travel_cost = self::distanceResponse($data['freelancer'], $data['appointment']['travelling_distance']);
            }
        }
        return $travel_cost;
    }

//    public static function setClassesResponse($classes, $timezone) {
//        $response = [];
//        if (!empty($classes)) {
//            foreach ($classes as $key => $class) {
//                $response[$key] = self::setClassResponse($class, $timezone);
//            }
//        }
//        return $response;
//    }
//
//    public static function setClassResponse($class, $timezone) {
//        $response = [];
//        if (!empty($class)) {
//            $response['uuid'] = $class['transaction_id'];
//            $response['freelancer_uuid'] = $class['freelancer_uuid'] ?? null;
//            $response['type'] = 'class';
//            $response['name'] = $class['class_book']['class_object']['name'] ?? '';
//            $response['id'] = !empty($class['transaction_id']) ? $class['transaction_id'] : null;
//            $response['transaction_id'] = !empty($class['transaction_id']) ? $class['transaction_id'] : null;
//            $response['travel_fee'] = !empty($class['appointment_freelancer']['can_travel']) ? self::distanceResponse($class['appointment_freelancer']) : null;
//            $response['date'] = CommonHelper::setDbDateFormat($class['class_book']['schedule']['class_date'], 'd M Y');
//            $response['time'] = CommonHelper::convertTimeToTimezone($class['class_book']['schedule']['from_time'], 'UTC', $timezone);
//            $response['amount'] = $class['class_book']['class_object']['price'];
//            $response['customer'] = $class['appointment_customer']['first_name']. ' ' . $class['appointment_customer']['last_name'];
//            $response['customer_uuid'] = $class['appointment_customer']['customer_uuid'];
//            $response['profile_image'] = !empty($class['appointment_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $class['appointment_customer']['profile_image'] : null;
//            $response['location'] = $class['appointment_customer']['address'] ?? '';
//            $response['payment_by'] = null;
//            $response['status'] = $class['class_book']['status'] ?? '';
//        }
//        return $response;
//    }

    public static function distanceResponse($data, $total_distance = 0) {
        $distance['distance'] = $total_distance;
        $distance['distance_cost'] = $data['travelling_cost_per_km'];
        $distance['total_distance_cost'] = $total_distance * $data['travelling_cost_per_km'];
        return $distance;
    }

    public static function preparePaymentRequestResposne($dataArray = [], $timezone = 'UTC') {

        $response = [];
        if (!empty($dataArray)) {
            foreach ($dataArray as $key => $value) {
                $response[$key]['profile_uuid'] = $value['user_uuid'];
                $response[$key]['requested_amount'] = $value['requested_amount'];
                $response[$key]['deductions'] = $value['deductions'];
                $response[$key]['final_amount'] = $value['final_amount'];
                $response[$key]['currency'] = $value['currency'];
                $response[$key]['status'] = "";

                if ($value['is_processed'] == 3) {
                    $response[$key]['status'] = "Cancelled";
                } elseif ($value['is_processed'] == 1) {
                    $response[$key]['status'] = "Processed";
                } elseif ($value['is_processed'] == 2) {
                    $response[$key]['status'] = "Transferred";
                } else {
                    $response[$key]['status'] = "Pending";
                }

                $response[$key]['notes_from_freelancer'] = $value['notes_from_freelancer'];
                $time = date('H:i:s', strtotime($value['datetime']));
                $response[$key]['time'] = CommonHelper::convertDateTimeToTimezone($time, 'UTC', $timezone);
            }
        }
        return $response;
    }

}

?>
