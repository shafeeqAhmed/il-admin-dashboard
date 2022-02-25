<?php

namespace App\Helpers;

use App\Freelancer;
use Carbon\Carbon;

Class AppointmentResponseHelper {
    /*
      |--------------------------------------------------------------------------
      | AppointmentResponseHelper that contains appointment related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use appointment processes
      |
     */

    /**
     * Description of AppointmentResponseHelper
     *
     * @author ILSA Interactive
     */
    public static function makeFreelancerAppointmentsResponse($data = [], $local_timezone = 'UTC', $login_user_type = '') {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $response[$key]['uuid'] = $value['appointment_uuid'];
                $response[$key]['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
                $response[$key]['purchased_package_uuid'] = !empty($value['purchased_package_uuid']) ? $value['purchased_package_uuid'] : null;
                $response[$key]['is_rescheduled_appointment'] = isset($value['has_rescheduled']) && ($value['has_rescheduled'] == 1) ? true : false;

//                $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']) && !empty($value['last_rescheduled_appointment']['rescheduled_by_uuid']) ? $value['last_rescheduled_appointment']['rescheduled_by_uuid'] : null;
//                $response[$key]['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']) && !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;
//                $response[$key]['rescheduled_by_uuid'] = null;
//                if (($value['has_rescheduled'] == true) && ($value['last_rescheduled_appointment']['rescheduled_by_type'] == "customer")) {
//                    $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['customer']['customer_uuid']) ? $value['last_rescheduled_appointment']['customer']['customer_uuid'] : null;
//                } else {
//                    $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['freelancer']['freelancer_uuid']) ? $value['last_rescheduled_appointment']['freelancer']['freelancer_uuid'] : null;
//                }
                $response[$key]['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_uuid');
                $response[$key]['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_type');

                $response[$key]['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;
                $response[$key]['title'] = $value['title'];
                $response[$key]['address'] = $value['address'];
                $response[$key]['lat'] = $value['lat'];
                $response[$key]['lng'] = $value['lng'];
                //$response[$key]['date'] = $value['appointment_date'];
                $response[$key]['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
                $response[$key]['start_time'] = $from_time_local_conversion;
                $response[$key]['end_time'] = $to_time_local_conversion;
                // $response[$key]['datetime'] = $value['appointment_date'] . ' ' . $from_time_local_conversion;
                $response[$key]['datetime'] = CommonHelper::getFullDateAndTime($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
                $response[$key]['price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $value['currency'], $value['appointment_freelancer']['default_currency']) : 0;
                $response[$key]['paid_amount'] = !empty($value['paid_amount']) ? (double) CommonHelper::getConvertedCurrency($value['paid_amount'], $value['currency'], $value['appointment_freelancer']['default_currency']) : 0;
                $response[$key]['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
                $response[$key]['status'] = $value['status'];
                $response[$key]['location_type'] = $value['location_type'];
//                $response[$key]['online_link'] = ($login_user_type == 'customer' && $value['status'] == 'cancelled') ? 'online video link': $value['online_link'];
                $response[$key]['purchase_time'] = $value['created_at'];
                $response[$key]['created_by'] = $value['created_by'] ?? '';
                $response[$key]['duration'] = self::calculateDuration($value);
                $response[$key]['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
                $response[$key]['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
                $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
                $response[$key]['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []));
                $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : []);
                if (!empty($value['appointment_customer'])) {
//                    $response[$key]['is_regular_customer'] = ($value['appointment_customer']['freelancer_id'] ==null && $value['appointment_customer']['freelancer_id'] =="")?1:0;
                    $response[$key]['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : []);
                    $response[$key]['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_customer']['profile_image'] : null;
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['user']['profile_image']);
                }
                if (!empty($value['appointment_walkin_customer'])) {
                    $response[$key]['is_regular_customer'] = 0;
                    $response[$key]['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : []);
                    $response[$key]['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_walkin_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_walkin_customer']['profile_image'] : null;
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
                }
            }
        }
        return $response;
    }

    public static function makeCalenderAppointmentsResponse($data = [], $date = null, $local_timezone = 'UTC') {
        $response = [];
        if (!empty($data)) {
            $key = 0;
            foreach ($data as $value) {
                if ($value['appointment_date'] == $date && ($value['status'] == 'pending' || $value['status'] == 'confirmed')) {
                    $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                    $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                    $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    $response[$key]['uuid'] = $value['appointment_uuid'];
                    $response[$key]['booking_id'] = $value['booking_identifier'];
                    $response[$key]['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
                    $response[$key]['title'] = $value['title'];
                    $response[$key]['address'] = $value['address'];
                    $response[$key]['lat'] = $value['lat'];
                    $response[$key]['lng'] = $value['lng'];
                    $response[$key]['date'] = $value['appointment_date'];
                    $response[$key]['start_time'] = $from_time_local_conversion;
                    $response[$key]['end_time'] = $to_time_local_conversion;
                    $response[$key]['datetime'] = $value['appointment_date'] . ' ' . $from_time_local_conversion;
                    $response[$key]['price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $value['currency'], $value['appointment_freelancer']['default_currency']) : 0;
                    $response[$key]['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
                    $response[$key]['status'] = $value['status'];
                    // $response[$key]['online_link'] = $value['online_link'];
                    $response[$key]['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
                    $response[$key]['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
                    $response[$key]['location_type'] = $value['location_type'];
                    $response[$key]['purchase_time'] = $value['created_at'];
                    $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
                    $response[$key]['duration'] = self::calculateDuration($value);
                    // $response[$key]['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []));
                    $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : []);
                    $response[$key]['created_by'] = $value['created_by'] ?? '';
                    if (!empty($value['appointment_customer'])) {
                        $response[$key]['is_regular_customer'] = ( $value['appointment_customer']['user']['freelancer_id'] == null && $value['appointment_customer']['user']['freelancer_id'] == "") ? 1 : 0;
                        $response[$key]['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : []);
                        $response[$key]['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_customer']['profile_image'] : null;
                        $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['user']['profile_image']);
                    }
                    if (!empty($value['appointment_walkin_customer'])) {
                        $response[$key]['is_regular_customer'] = 0;
                        $response[$key]['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : []);
                        $response[$key]['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_walkin_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_walkin_customer']['profile_image'] : null;
                        $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
                    }
                    $key++;
                }
            }
        }
        return $response;
    }

    public static function makeStatusBasedAppointmentsResponse($data = [], $inputs = [], $status = null) {
        $response = [];
        $local_timezone = $inputs['local_timezone'];
        if (!empty($data)) {
            $key = 0;
            $current_time_local_conversion = CommonHelper::convertTimeToTimezone(date('H:i:s'), 'UTC', $local_timezone);
            foreach ($data as $value) {
                if (!empty($status)) {
                    $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                    $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                    $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    // these line are commendted because if you single pending or conformed booking then you will miss it
//                    if (($status == 'pending' || $status == 'confirmed') && (($value['appointment_date'] < date('Y-m-d')) || ($value['appointment_date'] == date('Y-m-d')) && ($from_time_local_conversion < $current_time_local_conversion))) {
//                        continue;
//                    }
                    if (($status == 'history' || $status == 'past')) {
                        //  if (($value['appointment_start_date_time'] < strtotime(date("Y-m-d"))) || ($value['appointment_start_date_time'] == date("Y-m-d") && $from_time_local_conversion <= $current_time_local_conversion)) {
                        $response[$key] = self::makeHistoryAppointmentsResponse($value, $inputs);
                        $key++;
                        // }
                    } else {
                        $response[$key]['uuid'] = $value['appointment_uuid'];
                        $response[$key]['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
                        $response[$key]['purchased_package_uuid'] = !empty($value['purchased_package_uuid']) ? $value['purchased_package_uuid'] : null;
                        $response[$key]['is_rescheduled_appointment'] = ($value['has_rescheduled'] == 1) ? true : false;
//                        $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_uuid']) ? $value['last_rescheduled_appointment']['rescheduled_by_uuid'] : null;
//                        $response[$key]['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;

                        $response[$key]['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_uuid');
                        $response[$key]['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_type');

                        $response[$key]['title'] = $value['title'];
                        $response[$key]['address'] = $value['address'];
                        $response[$key]['lat'] = $value['lat'];
                        $response[$key]['lng'] = $value['lng'];
                        $response[$key]['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
                        $response[$key]['start_time'] = $from_time_local_conversion;
                        $response[$key]['end_time'] = $to_time_local_conversion;
                        $response[$key]['datetime'] = $value['appointment_date'] . ' ' . $from_time_local_conversion;
                        $response[$key]['price'] = (double) $value['price'];
                        $response[$key]['paid_amount'] = (double) $value['paid_amount'];
                        $response[$key]['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
                        $response[$key]['status'] = $value['status'];
                        //$response[$key]['online_link'] = $value['online_link'];
                        $response[$key]['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
                        $response[$key]['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
                        $response[$key]['location_type'] = $value['location_type'];
                        $response[$key]['purchase_time'] = $value['created_at'];
                        $response[$key]['created_by'] = $value['created_by'];
                        $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
                        $response[$key]['duration'] = self::calculateDuration($value);
                        //$response[$key]['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []));
                        $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : []);
                        if (!empty($value['appointment_customer'])) {
                            $response[$key]['is_regular_customer'] = ( $value['appointment_customer']['user']['freelancer_id'] == null && $value['appointment_customer']['user']['freelancer_id'] == "") ? 1 : 0;
                            $response[$key]['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : []);
                            $response[$key]['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_customer']['profile_image'] : null;
                            $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['user']['profile_image']);
                        }
                        if (!empty($value['appointment_walkin_customer'])) {
                            $response[$key]['is_regular_customer'] = 0;
                            $response[$key]['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : []);
                            $response[$key]['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_walkin_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_walkin_customer']['profile_image'] : null;
                            $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
                        }
                        $key++;
                    }
                }
            }
        }
        return $response;
    }

    public static function makeHistoryAppointmentsResponse($value = [], $filter_data = [], $chat_data = []) {


        $response = [];
        $local_timezone = $filter_data['local_timezone'];
        $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
        $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
        $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
        $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);

        $response['uuid'] = $value['appointment_uuid'];
        $response['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
        $response['purchased_package_uuid'] = !empty($value['purchased_package_uuid']) ? $value['purchased_package_uuid'] : null;
        $response['is_rescheduled_appointment'] = ($value['has_rescheduled'] == 1) ? true : false;

//        $response['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_uuid']) ? $value['last_rescheduled_appointment']['rescheduled_by_uuid'] : null;
//        $response['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;

        $response['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_uuid');
        $response['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_type');

        $response['title'] = $value['title'];
        $response['address'] = $value['address'];
        $response['lat'] = $value['lat'];
        $response['lng'] = $value['lng'];

        $response['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
        $response['start_time'] = $from_time_local_conversion;
        $response['end_time'] = $to_time_local_conversion;
        $response['datetime_utc'] = $value['appointment_date'] . ' ' . $value['from_time'];
        $response['datetime'] = $value['appointment_date'] . ' ' . $from_time_local_conversion;
        $response['price'] = (double) $value['price'];
        $response['paid_amount'] = (double) $value['paid_amount'];
        $response['actual_price'] = (double) $value['price'];

        if (isset($filter_data['login_user_type']) && $filter_data['login_user_type'] == "freelancer") {
//            $response['price'] = (double) $value['price'];
//            $response['paid_amount'] = (double) $value['paid_amount'];
//            $response['actual_price'] = (double) $value['price'];
            $response['price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $value['currency'], $filter_data['currency']) : 0;
            $response['paid_amount'] = !empty($value['paid_amount']) ? (double) CommonHelper::getConvertedCurrency($value['paid_amount'], $value['currency'], $filter_data['currency']) : 0;
            $response['actual_price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $value['currency'], $filter_data['currency']) : 0;
        }

        $response['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
        $response['status'] = $value['status'];
        // $response['online_link'] = $value['online_link'];
        $response['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
        $response['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
        $response['location_type'] = $value['location_type'];
        $response['purchase_time'] = $value['created_at'];
        $response['created_by'] = $value['created_by'] ?? '';

        $response['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
        $response['duration'] = self::calculateDuration($value);
        // $response['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []));

        $response['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : [], $chat_data);

        if (!empty($value['appointment_customer'])) {
            $response['is_regular_customer'] = 1;
            $response['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : [], $chat_data);
            $response['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
            $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['user']['profile_image']);
        }
        if (!empty($value['appointment_walkin_customer'])) {
            $response['is_regular_customer'] = 0;
            $response['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : [], $chat_data);
            $response['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
            $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
        }
        return $response;
    }

    public static function upcomingAppointmentsResponse($data = [], $local_timezone = 'UTC') {
        $response = [];
        if (!empty($data)) {
            $key = 0;
            $date = strtotime(date("Y-m-d H:i:s"));
            $current_time_local_conversion = CommonHelper::convertTimeToTimezone(date('H:i:s'), 'UTC', $local_timezone);

            foreach ($data as $value) {

                //if ($value['appointment_date'] >= $date) {
                //Change the check point in this conditon
                if ($value['appointment_start_date_time'] >= $date) {
                    $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                    $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                    $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    //change the check point int this also
                    //if ($value['appointment_date'] == $date && $from_time_local_conversion <= $current_time_local_conversion) {

                    if ($value['appointment_date'] == $date/* && $from_time_local_conversion <= $current_time_local_conversion */) {
                        continue;
                    }
                    if ($value['status'] == 'pending' || $value['status'] == 'confirmed') {
                        $response[$key]['uuid'] = $value['appointment_uuid'];
                        $response[$key]['is_rescheduled_appointment'] = (isset($value['has_rescheduled']) && $value['has_rescheduled'] == 1) ? true : false;
//                      $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_uuid']) ? $value['last_rescheduled_appointment']['rescheduled_by_uuid'] : null;
//                        $response[$key]['rescheduled_by_uuid'] = self::getRescheduledUuid(!empty($value['last_rescheduled_appointment']) ? $value['last_rescheduled_appointment'] : null);
//                        $response[$key]['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;

                        $response[$key]['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_uuid');
                        $response[$key]['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_type');

                        $response[$key]['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
                        $response[$key]['purchased_package_uuid'] = !empty($value['purchased_package_uuid']) ? $value['purchased_package_uuid'] : null;
                        $response[$key]['title'] = $value['title'];
                        $response[$key]['address'] = $value['address'];
                        $response[$key]['lat'] = $value['lat'];
                        $response[$key]['lng'] = $value['lng'];
                        $response[$key]['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
                        $response[$key]['start_time'] = $from_time_local_conversion;
                        $response[$key]['end_time'] = $to_time_local_conversion;
                        $response[$key]['datetime'] = CommonHelper::getFullDateAndTime($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);
                        $response[$key]['price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $value['currency'], $value['appointment_freelancer']['default_currency']) : 0;
                        $response[$key]['paid_amount'] = !empty($value['paid_amount']) ? (double) CommonHelper::getConvertedCurrency($value['paid_amount'], $value['currency'], $value['appointment_freelancer']['default_currency']) : 0;
                        $response[$key]['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
                        $response[$key]['status'] = $value['status'];
                        //$response[$key]['online_link'] = $value['online_link'];
                        $response[$key]['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
                        $response[$key]['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
                        $response[$key]['location_type'] = $value['location_type'];
                        $response[$key]['purchase_time'] = $value['created_at'];
                        $response[$key]['created_by'] = $value['created_by'] ?? '';
                        $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);

                        $response[$key]['duration'] = self::calculateDuration($value);

                        //$response[$key]['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []), $value['currency'], $value['appointment_freelancer']['default_currency']);

                        $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : []);
                        if (!empty($value['appointment_customer'])) {

                            $response[$key]['is_regular_customer'] = ( $value['appointment_customer']['user']['freelancer_id'] == null && $value['appointment_customer']['user']['freelancer_id'] == "") ? 1 : 0;
                            $response[$key]['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : []);
                            $response[$key]['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['user']['first_name'] . ' ' . $value['appointment_customer']['user']['last_name'] : null;
                            $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['user']['profile_image']);
                        }
                        if (!empty($value['appointment_walkin_customer'])) {
                            $response[$key]['is_regular_customer'] = 0;
                            $response[$key]['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : []);
                            $response[$key]['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
                            $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
                        }
                        $key++;
                    }
                }
            }
            return $response;
        }
    }

    public static function getRescheduledUuid($reschedule) {
        $uuid = '';
        if ($reschedule) {
            if ($reschedule['rescheduled_by_type'] == 'customer') {

                $uuid = CommonHelper::getUuidById('customers', 'id', $reschedule['rescheduled_by_id'], 'customer_uuid');
            } elseif ($reschedule['rescheduled_by_type'] == 'freelancer') {

                $uuid = CommonHelper::getUuidById('freelancers', 'id', $reschedule['rescheduled_by_id'], 'freelancer_uuid');
            } elseif ($reschedule['rescheduled_by_type'] == 'admin') {
                $uuid = CommonHelper::getUuidById('admin', 'id', $reschedule['rescheduled_by_id'], 'user_uuid');
            }
        }

        return $uuid;
    }

    public static function calculateDurationInMinutes($data) {
        $startDate = CommonHelper::getFullDateAndTime($data['appointment_start_date_time'], $data['saved_timezone'], $data['local_timezone']);
        $endDate = CommonHelper::getFullDateAndTime($data['appointment_end_date_time'], $data['saved_timezone'], $data['local_timezone']);
        return ((strtotime($endDate) - strtotime($startDate))/60);
    }

    public static function calculateDuration($data) {
        $difference = '';
        $startDate = CommonHelper::getFullDateAndTime($data['appointment_start_date_time'], $data['saved_timezone'], $data['local_timezone']);
        $endDate = CommonHelper::getFullDateAndTime($data['appointment_end_date_time'], $data['saved_timezone'], $data['local_timezone']);
        $diff = self::calculateDateTimeDiffence($startDate, $endDate);
        if ($diff->m > 0) {
            $label = ($diff->m > 1) ? ' Months' : ' Month';
            $difference = $difference . ' ' . $diff->m . $label;
        }
        if ($diff->d > 0) {
            $label = ($diff->d > 1) ? ' Days' : ' Day';
            $difference = $difference . ' ' . $diff->d . $label;
        }
        if ($diff->h > 0) {
            $label = ($diff->h > 1) ? ' Hours' : ' Hour';
            $difference = $difference . ' ' . $diff->h . $label;
        }
        if ($diff->i > 0) {
            $label = ($diff->i > 1) ? ' Minute' : ' Mint';
            $difference = $difference . ' ' . $diff->i . $label;
        }

        return $difference;
    }

    public static function calculateDateTimeDiffence($startDate, $endDate) {
        $t1 = Carbon::parse($endDate);
        $t2 = Carbon::parse($startDate);
        $diff = $t1->diff($t2);

        return $diff;
    }

    public static function customerUpcomingClassAppointmentsResponse($data = [], $local_timezone = 'UTC', $customer = [], $status = null) {

        $response = [];
        if (!empty($data)) {
            $key = 0;
            //dd(date("Y-m-d H:i:s"));
            $date = strtotime(date("Y-m-d H:i:s"));

            $current_time_local_conversion = CommonHelper::convertTimeToTimezone(date('H:i:s'), 'UTC', $local_timezone);
            foreach ($data as $value) {

                // dd($value);
                // change the check conditon
//                if ($value['class_date'] >= $date) {
                if ($value['start_date_time'] != "" && $value['start_date_time'] != null && ($value['start_date_time'] > $date || $value['start_date_time'] == $date)) {


                    //INFO this day try to show only this day or grater then today class appointment
                    //TODO:we will fix and get all class appointment with query no need any conditon
                    //if ($value['class_date'] >= $date) {
                    //TODO: we will remove these convert function when our fully funcational and proper working
                    $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                    $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                    $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                    $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);

                    //if (($value['class_date'] == $date && $from_time_local_conversion <= $current_time_local_conversion) || ($value['status'] == 'cancelled' || $value['status'] == 'rejected' || $value['status'] == 'completed')) {
//                    if (($value['start_date_time'] !="" && $value['start_date_time'] !=null && $value['start_date_time'] == $date /*&& $from_time_local_conversion <= $current_time_local_conversion*/) || ($value['status'] == 'cancelled' || $value['status'] == 'rejected' || $value['status'] == 'completed')) {
//
//                        continue;
//                    }


                    $response[$key]['uuid'] = (isset($value['class_uuid'])) ? $value['class_uuid'] : CommonHelper::getRecordByUuid('classes', 'id', $value['class_id'], 'class_uuid');
                    $response[$key]['is_rescheduled_appointment'] = false;
                    $response[$key]['class_schedule_uuid'] = $value['class_schedule_uuid'];
                    $response[$key]['title'] = $value['classes']['name'];
                    $response[$key]['address'] = $value['classes']['address'];
                    $response[$key]['lat'] = $value['classes']['lat'];
                    $response[$key]['lng'] = $value['classes']['lng'];
                    //$response[$key]['package_uuid'] = AppointmentResponseHelper::getCustomerUpcomingClassPackageUuid($value, $customer);
                    $response[$key]['class_image'] = !empty($value['classes']['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['class_images'] . $value['classes']['image'] : null;
                    //$response[$key]['date'] = $value['class_date'];
                    $response[$key]['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['start_date_time'], 'UTC', $value['local_timezone']);
                    $response[$key]['start_time'] = $from_time_local_conversion;
                    $response[$key]['end_time'] = $to_time_local_conversion;
                    //$response[$key]['datetime'] = $value['class_date'] . ' ' . $from_time_local_conversion;
                    $response[$key]['datetime'] = CommonHelper::getFullDateAndTime($value['start_date_time'], 'UTC', $value['local_timezone']);
                    $response[$key]['price'] = !empty($value['classes']['price']) ? (double) CommonHelper::getConvertedCurrency($value['classes']['price'], $value['classes']['currency'], $value['classes']['freelancer']['default_currency']) : 0;
                    $response[$key]['type'] = 'class';
                    $response[$key]['status'] = $status;
                    // $response[$key]['online_link'] = $value['status'] == 'cancelled' ? 'online video link' : $value['online_link'];
                    $response[$key]['total_session'] = 1;
                    $response[$key]['session_number'] = 1;
                    $response[$key]['package'] = null;
                    $response[$key]['purchase_time'] = null;
                    if (!empty($value['class_bookings'])) {
                        foreach ($value['class_bookings'] as $index => $single_booking) {
                            $classSchedulesuuid = CommonHelper::getRecordByUuid('class_schedules', 'id', $single_booking['class_schedule_id'], 'class_schedule_uuid');
                            if ($classSchedulesuuid == $value['class_schedule_uuid'] && $single_booking['customer_id'] == $customer['id']) {
                                if ($single_booking['status'] == "confirmed") {
                                    $response[$key]['class_booking_uuid'] = $single_booking['class_booking_uuid'];
                                    $response[$key]['package_uuid'] = $single_booking['package']['package_uuid'] ?? '';
                                    $response[$key]['purchase_time'] = $single_booking['created_at'];
                                    $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($single_booking['package']) ? $single_booking['package'] : []), $single_booking);
                                    $response[$key]['total_session'] = !empty($single_booking['total_session']) ? $single_booking['total_session'] : 1;
                                    $response[$key]['session_number'] = !empty($single_booking['session_number']) ? $single_booking['session_number'] : 1;
                                    break;
                                }
                                $response[$key]['online_link'] = $single_booking['status'] == 'cancelled' ? 'online video link' : $value['online_link'];
                            }
                        }
                    } elseif (!empty($value['package'])) {
                        $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
                    }
                    $response[$key]['duration'] = self::calculateDuration($value);
                    //$response[$key]['service'] = self::appointmentServiceResponse((!empty($value['classes']['freelance_category']) ? $value['classes']['freelance_category'] : []), $value['classes']['currency'], $value['classes']['freelancer']['default_currency']);

                    $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['classes']['freelancer']) ? $value['classes']['freelancer'] : []);
                    $response[$key]['customer'] = self::appointmentCustomerResponse([]);
                    $response[$key]['customer_name'] = null;
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse();
                    if (!empty($customer)) {
                        $response[$key]['customer'] = self::appointmentCustomerResponse($customer);
                        $response[$key]['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                        $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($customer['profile_image']);
                    }
                    $key++;
                }
            }
            return $response;
        }
        //}
    }

    public static function getCustomerUpcomingClassPackageUuid($schedule = [], $customer = []) {
        $package_uuid = null;
        if (!empty($customer) && !empty($schedule['class_bookings'])) {
            foreach ($schedule['class_bookings'] as $booking) {

                if (!empty($customer['id']) && ($booking['customer_id'] == $customer['id'])) {

                    $package_uuid = $booking['package_id'];
                    break;
                } elseif (!empty($customer['walkin_customer_uuid']) && ($booking['customer_uuid'] == $customer['walkin_customer_uuid'])) {
                    $package_uuid = $booking['package_id'];
                    break;
                }
            }
        }
        return $package_uuid;
    }

    public static function appointmentDetailsResponse($data = [], $to_currency = 'SAR', $local_timezone = 'UTC') {

        $response = [];
        $ids['customer_uuid'] = null;
        $ids['freelancer_uuid'] = null;
        if (!empty($data)) {
            $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($data['from_time'], $data['saved_timezone'], $data['local_timezone']);
            $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $data['local_timezone'], $local_timezone);
            $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($data['to_time'], $data['saved_timezone'], $data['local_timezone']);
            $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $data['local_timezone'], $local_timezone);

            $response['uuid'] = $data['appointment_uuid'];
            $response['appointment_id'] = $data['booking_identifier'];
            $response['package_uuid'] = !empty($data['package_uuid']) ? $data['package_uuid'] : null;

            $response['purchased_package_uuid'] = !empty($data['purchased_package_uuid']) ? $data['purchased_package_uuid'] : null;
//            $response['purchased_package_uuid'] = !empty($data['last_rescheduled_appointment']) ? $data['purchased_package_uuid'] : null;
            $response['is_rescheduled_appointment'] = (isset($data['has_rescheduled']) && $data['has_rescheduled'] == 1) ? true : false;
            //            $response['rescheduled_by_uuid'] = !empty($data['last_rescheduled_appointment']['rescheduled_by_id']) ? CommonHelper::getRecordByUserType($data['last_rescheduled_appointment']['rescheduled_by_type'], $data['last_rescheduled_appointment']['rescheduled_by_id'], ($data['last_rescheduled_appointment']['rescheduled_by_type'] == 'freelancer') ? 'freelancer_uuid' : 'customer_uuid', 'id') : null;
//            $response['rescheduled_by_type'] = !empty($data['last_rescheduled_appointment']['rescheduled_by_type']) ? $data['last_rescheduled_appointment']['rescheduled_by_type'] : null;

            $response['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($data, 'rescheduled_by_uuid');
            $response['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($data, 'rescheduled_by_type');
            $response['title'] = $data['title'];
            $response['address'] = $data['address'];
            $response['lat'] = $data['lat'];
            $response['lng'] = $data['lng'];
            //$response['date'] = $data['appointment_date'];
            $response['date'] = CommonHelper::convertMyDBDateIntoLocalDate($data['appointment_start_date_time'], 'UTC', $data['local_timezone']);
            $response['end_date'] = CommonHelper::convertMyDBDateIntoLocalDate($data['appointment_end_date_time'], 'UTC', $data['local_timezone']);
            $response['start_time'] = $from_time_local_conversion;
            $response['end_time'] = $to_time_local_conversion;
            $response['boat_discount_hours'] = $data['boat_discount_hours'];
            $response['boat_discount_hours_percentage'] = $data['boat_discount_hours_percentage'];
            $response['price_per_half_hour'] = $data['price_per_half_hour'];

            $response['has_review'] = !empty($data['review']) ? true : false;
            if (!empty($data['review'])) {
                $data['customer'] = $data['appointment_customer'];
                $data['appointment'] = $data;
                $response['review'] = self::prepareReviewResponse($data);
            }
            //$response['datetime'] = $data['appointment_date'] . ' ' . $from_time_local_conversion;
            $response['datetime'] = CommonHelper::getFullDateAndTime($data['appointment_start_date_time'], 'UTC', $data['local_timezone']);
//            $response['price'] = !empty($data['price']) ? (double) CommonHelper::getConvertedCurrency($data['price'], $data['currency'], $to_currency) : 0;
            $response['promo_code'] = isset($data['promo_code']) && !is_null($data['promo_code']) ? $data['promo_code']['coupon_code'] : null;

            if (isset($data['transaction'])) {
                if (strtolower($data['transaction']['to_currency']) == strtolower($to_currency)) {
                    $response['actual_price'] = $data['price'];
                    $response['paid_amount'] = $data['paid_amount'];
                } else {
                    if ($data['transaction']['from_currency'] == $data['transaction']['to_currency']) {
                        $response['actual_price'] = $data['price'] * $data['transaction']['exchange_rate'];
                        $response['paid_amount'] = $data['paid_amount'] * $data['transaction']['exchange_rate'];
                    } else {
                        $response['actual_price'] = $data['price'] / $data['transaction']['exchange_rate'];
                        $response['paid_amount'] = $data['paid_amount'] / $data['transaction']['exchange_rate'];
                    }
                }
            } else {
                $response['actual_price'] = !empty($data['price']) ? (double) CommonHelper::getConvertedCurrency($data['price'], $data['currency'], $to_currency) : 0;
                $response['paid_amount'] = !empty($data['paid_amount']) ? (double) CommonHelper::getConvertedCurrency($data['paid_amount'], $data['currency'], $to_currency) : 0;
            }

            $response['type'] = $data['type'];
            $response['status'] = $data['status'];
            // $response['online_link'] = $data['online_link'];
            $response['total_session'] = !empty($data['total_session']) ? $data['total_session'] : 1;
            $response['session_number'] = !empty($data['session_number']) ? $data['session_number'] : 1;
            $response['location_type'] = $data['location_type'];
            $response['notes'] = $data['notes'];
            $response['created_by'] = $data['created_by'] ?? '';
            //$response['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($data['package']) ? $data['package'] : []), $data);
            // $response['service'] = self::appointmentServiceResponse((!empty($data['appointment_service']) ? $data['appointment_service'] : []), $data['appointment_freelancer']['default_currency'], $to_currency);
            $response['duration'] = self::calculateDuration($data);
            $response['duration_minutes'] = (int) self::calculateDurationInMinutes($data);

            if (!empty($data['appointment_customer']) || !empty($data['appointment_walkin_customer']) && !empty($data['appointment_freelancer'])) {
                $ids['customer_id'] = !empty($data['appointment_customer']) ? CommonHelper::getCutomerIdByUuid($data['appointment_customer']['customer_uuid']) : $data['appointment_walkin_customer']['walkin_customer_uuid'];
                $ids['freelancer_id'] = !empty($data['appointment_freelancer']) ? CommonHelper::getFreelancerIdByUuid($data['appointment_freelancer']['freelancer_uuid']) : null;
            }

            $chat_data = ClientHelper::getClientChatData($ids);

            $response['freelancer'] = self::appointmentFreelancerResponse(!empty($data['appointment_freelancer']) ? $data['appointment_freelancer'] : [], $chat_data);
            if (!empty($data['appointment_customer'])) {

                $response['is_regular_customer'] = ( $data['appointment_customer']['user']['freelancer_id'] == null && $data['appointment_customer']['user']['freelancer_id'] == "") ? 1 : 0;
                $response['customer'] = self::appointmentCustomerResponse(!empty($data['appointment_customer']) ? $data['appointment_customer'] : [], $chat_data);
                $response['customer_name'] = !empty($data['appointment_customer']) ? $data['appointment_customer']['user']['first_name'] . ' ' . $data['appointment_customer']['user']['last_name'] : null;
                $response['profile_type'] = ( $data['appointment_customer']['user']['freelancer_id'] == null && $data['appointment_customer']['user']['freelancer_id'] == "") ? "customer" : 'walkin_customer';
                $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['appointment_customer']['user']['profile_image']);
            }
            if (!empty($data['appointment_walkin_customer'])) {
                $response['is_regular_customer'] = 0;
                $response['customer'] = self::appointmentWalkinCustomerResponse(!empty($data['appointment_walkin_customer']) ? $data['appointment_walkin_customer'] : [], $chat_data);
                $response['customer_name'] = !empty($data['appointment_walkin_customer']) ? $data['appointment_walkin_customer']['first_name'] . ' ' . $data['appointment_walkin_customer']['last_name'] : null;
                $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['appointment_walkin_customer']['profile_image']);
                $response['profile_type'] = "walkin_customer";
            }
//            if (!empty($data['appointment_customer']) || !empty($data['appointment_walkin_customer']) && !empty($data['appointment_freelancer'])) {
//                $ids['customer_uuid'] = !empty($data['appointment_customer']) ? $data['appointment_customer']['customer_uuid'] : $data['appointment_walkin_customer']['customer_uuid'];
//                $ids['customer_uuid'] = !empty($data['appointment_freelancer']) ? $data['appointment_freelancer']['freelancer_uuid'] : null;
//                $chat_data = ClientHelper::getClientChatData($ids);
//                $response['has_subscribed'] = $chat_data['has_subscription'];
//                $response['has_appointment'] = $chat_data['has_appointment'];
//                $response['has_followed'] = $chat_data['has_followed'];
//            }
        }
        return $response;
    }

    public static function getValueFromLastScheduleRecord($data, $value) {
        $rescheduled = !empty($data['last_rescheduled_appointment']) ? $data['last_rescheduled_appointment'] : null;
        $val = null;
        if ($rescheduled) {
            if ($value == 'rescheduled_by_uuid') {
                if ($rescheduled['rescheduled_by_type'] == 'customer') {
                    $val = CommonHelper::getUuidById('customers', 'id', $rescheduled['rescheduled_by_id'], 'customer_uuid');
                } elseif ($rescheduled['rescheduled_by_type'] == 'freelancer') {
                    $val = CommonHelper::getUuidById('freelancers', 'id', $rescheduled['rescheduled_by_id'], 'freelancer_uuid');
                } elseif ($rescheduled['rescheduled_by_type'] == 'admin') {
                    // we decided if rescheduled_by_type is admin then we will return freelancer_uuid from appointment table freelancer_id because
                    //admin belong to user table and does not have association with freelancer
                    //So in case of amdin we will return freelancer_uuid instead of user_uuid
                    $val = CommonHelper::getUuidById('freelancers', 'id', $data['freelancer_id'], 'freelancer_uuid');
                }
            } else {
                $val = $rescheduled['rescheduled_by_type'];
            }
        }
        return $val;
    }

    public static function prepareReviewResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['review_uuid'] = !empty($data['review']['review_uuid']) ? $data['review']['review_uuid'] : null;
            $response['reviewer']['reviewer_uuid'] = !empty($data['review']['reviewer_id']) ? CommonHelper::getCutomerUUIDByid($data['review']['reviewer_id']) : null;
            $response['date'] = !empty($data['review']['created_at']) ? $data['review']['created_at'] : null;
            $response['reviewer']['first_name'] = !empty($data['appointment_customer']['user']['first_name']) ? $data['appointment_customer']['user']['first_name'] : null;
            $response['reviewer']['last_name'] = !empty($data['appointment_customer']['user']['last_name']) ? $data['appointment_customer']['user']['last_name'] : null;
            $response['title'] = !empty($data['title']) ? $data['title'] : null;
            $response['rating'] = !empty($data['review']['rating']) ? $data['review']['rating'] : 0;
            $response['review'] = !empty($data['review']['review']) ? $data['review']['review'] : null;
            $response['type'] = !empty($data['review']['type']) ? $data['review']['type'] : null;
            $response['reviewer']['profile_images'] = !empty($data['appointment_customer']['user']['profile_image']) ? CustomerResponseHelper::customerProfileImagesResponse($data['appointment_customer']['user']['profile_image']) : [];
            $response['reply'] = self::getReviewReplyResponse(!empty($data['review']['reply']) ? $data['review']['reply'] : null);
            $time_con = CommonHelper::convertTimeToTimezone($data['from_time'], $data['saved_timezone'], $data['local_timezone']);
            $response['appointment_date'] = $data['appointment_date'] . ' ' . $time_con;
        }
        return $response;
    }

    public static function prepareAppointmentResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['uuid'] = !empty($data['appointment_uuid']) ? $data['appointment_uuid'] : null;
            $response['paid_amount'] = !empty($data['paid_amount']) ? (double) ($data['paid_amount']) : null;
        }
        return $response;
    }

    public static function getReviewReplyResponse($data = []) {
        $response = null;
        if (!empty($data)) {
            $response['reply_uuid'] = $data['reply_uuid'];
            $response['user_uuid'] = !empty($data['user']['user_customer']['customer_uuid']) ? $data['user']['user_customer']['customer_uuid'] : $data['user']['user_freelancer']['freelancer_uuid'];
            $response['reply'] = !empty($data['reply']) ? $data['reply'] : null;
            $response['date'] = date("Y-m-d", strtotime($data['created_at']));
            if (!empty($data['user']['user_customer'])) {
                $response['first_name'] = !empty($data['user']['user_customer']['first_name']) ? $data['user']['user_customer']['first_name'] : null;
                $response['last_name'] = !empty($data['user']['user_customer']['last_name']) ? $data['user']['user_customer']['last_name'] : null;
                $response['profile_image'] = !empty($data['user']['user_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $data['user']['user_customer']['profile_image'] : null;
                $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['appointment_customer']['user']['profile_image']);
            }
            if (!empty($data['user']['user_freelancer'])) {
                $response['first_name'] = !empty($data['user']['user_freelancer']['first_name']) ? $data['user']['user_freelancer']['first_name'] : null;
                $response['last_name'] = !empty($data['user']['user_freelancer']['last_name']) ? $data['user']['user_freelancer']['last_name'] : null;
                $response['profile_image'] = !empty($data['user']['user_freelancer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['user']['user_freelancer']['profile_image'] : null;
                $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['user']['user_freelancer']['profile_image']);
            }
        }
        return $response;
    }

    public static function appointmentFreelancerResponse($freelancer = [], $chat_data = []) {
        $response = null;
        $response['freelancer_uuid'] = $freelancer['freelancer_uuid'];
        $response['first_name'] = $freelancer['first_name'];
        $response['last_name'] = $freelancer['last_name'];
        $response['boat_name'] = $freelancer['first_name'] . ' ' . $freelancer['last_name'];

        $response['username'] = !empty($freelancer['user']) ? $freelancer['user']['first_name'] . ' ' . $freelancer['user']['last_name'] : null;

        //   $response['public_chat'] = ($freelancer['public_chat'] == 1) ? true : false;
//        $response['profile_image'] = !empty($freelancer['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $freelancer['profile_image'] : null;
        $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($freelancer['profile_image']);
        $response['profession_details'] = [];
//        $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($freelancer['profession']) ? $freelancer['profession'] : []);
        if (!empty($chat_data)) {
            $response['has_subscribed'] = ($chat_data['has_subscription'] == 1) ? true : false;
            $response['has_appointment'] = ($chat_data['has_appointment'] == 1) ? true : false;
            //$response['has_followed'] = ($chat_data['has_followed'] == 1) ? true : false;
        }
        return $response;
    }

    public static function appointmentCustomerResponse($customer = [], $chat_data = []) {

        $response = null;
        if (!empty($customer)) {
            $response['customer_uuid'] = null;
            if (!empty($customer['customer_uuid'])) {
                $response['customer_uuid'] = $customer['customer_uuid'];
            } elseif (!empty($customer['walkin_customer_uuid'])) {
                $response['customer_uuid'] = $customer['walkin_customer_uuid'];
            }

            $response['user_uuid'] = $customer['user']['user_uuid'];
            $response['first_name'] = $customer['user']['first_name'];
            $response['last_name'] = $customer['user']['last_name'];
            $response['public_chat'] = (isset($customer['public_chat']) == 1) ? true : false;

//        $response['profile_image'] = !empty($customer['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $customer['profile_image'] : null;
            $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($customer['user']['profile_image']);
            if (!empty($chat_data)) {
                $response['has_subscribed'] = ($chat_data['has_subscription'] == 1) ? true : false;
                $response['has_appointment'] = ($chat_data['has_appointment'] == 1) ? true : false;
                // $response['has_followed'] = ($chat_data['has_followed'] == 1) ? true : false;
            }
        }
        return $response;
    }

    public static function appointmentWalkinCustomerResponse($customer = [], $chat_data = []) {
        $response = [];
        $response['customer_uuid'] = $customer['walkin_customer_uuid'];
        $response['first_name'] = $customer['first_name'];
        $response['last_name'] = $customer['last_name'];
        $response['public_chat'] = isset($customer['public_chat']) ? (($customer['public_chat'] == 1) ? true : false) : false;
//        $response['profile_image'] = !empty($customer['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $customer['profile_image'] : null;
        $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($customer['profile_image']);
        if (!empty($chat_data)) {
            $response['has_subscribed'] = ($chat_data['has_subscription'] == 1) ? true : false;
            $response['has_appointment'] = ($chat_data['has_appointment'] == 1) ? true : false;
            $response['has_followed'] = ($chat_data['has_followed'] == 1) ? true : false;
        }
        return $response;
    }

    public static function searchAppointmentsResponse($data = [], $local_timezone = 'UTC') {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $response[$key]['uuid'] = $value['appointment_uuid'];
                $response[$key]['package_uuid'] = !empty($value['package_uuid']) ? $value['package_uuid'] : null;
                $response[$key]['purchased_package_uuid'] = !empty($value['purchased_package_uuid']) ? $value['purchased_package_uuid'] : null;
                $response[$key]['is_rescheduled_appointment'] = ($value['has_rescheduled'] == 1) ? true : false;

//                $response[$key]['rescheduled_by_uuid'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_uuid']) ? $value['last_rescheduled_appointment']['rescheduled_by_uuid'] : null;
//                $response[$key]['rescheduled_by_type'] = !empty($value['last_rescheduled_appointment']['rescheduled_by_type']) ? $value['last_rescheduled_appointment']['rescheduled_by_type'] : null;

                $response[$key]['rescheduled_by_uuid'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_uuid');
                $response[$key]['rescheduled_by_type'] = self::getValueFromLastScheduleRecord($value, 'rescheduled_by_type');

                $response[$key]['title'] = $value['title'];
                $response[$key]['address'] = $value['address'];
                $response[$key]['lat'] = $value['lat'];
                $response[$key]['lng'] = $value['lng'];
                //$response[$key]['date'] = $value['appointment_date'];
                $response[$key]['date'] = CommonHelper::convertMyDBDateIntoLocalDate($value['appointment_start_date_time'], 'UTC', $value['local_timezone']);

                $response[$key]['start_time'] = $from_time_local_conversion;
                $response[$key]['end_time'] = $to_time_local_conversion;
                $response[$key]['price'] = (double) $value['price'];
                $response[$key]['type'] = ($value['type'] == 'normal' ? 'appointment' : $value['type']);
                $response[$key]['status'] = $value['status'];
                $response[$key]['online_link'] = $value['online_link'];
                $response[$key]['total_session'] = !empty($value['total_session']) ? $value['total_session'] : 1;
                $response[$key]['session_number'] = !empty($value['session_number']) ? $value['session_number'] : 1;
                $response[$key]['location_type'] = $value['location_type'];
                $response[$key]['created_by'] = $value['created_by'] ?? '';
                $response[$key]['package'] = PackageResponseHelper::appointmentPackageResponse((!empty($value['package']) ? $value['package'] : []), $value);
                $response[$key]['service'] = self::appointmentServiceResponse((!empty($value['appointment_service']) ? $value['appointment_service'] : []));
                if (!empty($value['appointment_service'])) {
                    $response[$key]['service'] = self::appointmentServiceResponse(!empty($value['appointment_service']) ? $value['appointment_service'] : []);
                }
                if (!empty($value['appointment_freelancer'])) {
                    $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['appointment_freelancer']) ? $value['appointment_freelancer'] : []);
                }
                if (!empty($value['appointment_customer'])) {
                    $response[$key]['is_regular_customer'] = ( $value['appointment_customer']['freelancer_id'] == null && $value['appointment_customer']['freelancer_id'] == "") ? 1 : 0;
                    $response[$key]['customer'] = self::appointmentCustomerResponse(!empty($value['appointment_customer']) ? $value['appointment_customer'] : []);
                    $response[$key]['customer_name'] = !empty($value['appointment_customer']) ? $value['appointment_customer']['first_name'] . ' ' . $value['appointment_customer']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_customer']['profile_image'] : null;
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_customer']['profile_image']);
                }
                if (!empty($value['appointment_walkin_customer'])) {
                    $response[$key]['is_regular_customer'] = 0;
                    $response[$key]['customer'] = self::appointmentWalkinCustomerResponse(!empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer'] : []);
                    $response[$key]['customer_name'] = !empty($value['appointment_walkin_customer']) ? $value['appointment_walkin_customer']['first_name'] . ' ' . $value['appointment_walkin_customer']['last_name'] : null;
//                    $response[$key]['profile_image'] = !empty($value['appointment_walkin_customer']['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $value['appointment_walkin_customer']['profile_image'] : null;
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['appointment_walkin_customer']['profile_image']);
                }
            }
        }
        return $response;
    }

    public static function appointmentServiceResponse($service = [], $from_currency = null, $to_currency = null) {
        $response = null;
        if (!empty($service)) {
            $response['service_uuid'] = $service['freelancer_category_uuid'];
            $response['name'] = $service['name'];
            (double) $service['price'];
            $response['price'] = $service['price'];
            if (!empty($from_currency) && !empty(!empty($to_currency))) {
                $response['price'] = !empty($service['price']) ? (double) CommonHelper::getConvertedCurrency($service['price'], $from_currency, $to_currency) : 0;
            }
            $response['duration'] = $service['duration'];
            // $response['description'] = $service['description'];
            $response['is_online'] = $service['is_online'];
            $response['description_video'] = !empty($service['description_video']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_category_video'] . $service['description_video'] : null;
            $response['description_video_thumbnail'] = !empty($service['description_video_thumbnail']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_category_video'] . $service['description_video_thumbnail'] : null;
        }
        return $response;
    }

    public static function customerAllClassAppointmentsResponse($data = [], $local_timezone = 'UTC', $customer = []) {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $from_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['from_time'], $value['saved_timezone'], $value['local_timezone']);
                $from_time_local_conversion = CommonHelper::convertTimeToTimezone($from_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $to_time_saved_conversion = CommonHelper::convertTimeToTimezone($value['to_time'], $value['saved_timezone'], $value['local_timezone']);
                $to_time_local_conversion = CommonHelper::convertTimeToTimezone($to_time_saved_conversion, $value['local_timezone'], $local_timezone);
                $response[$key]['uuid'] = $value['class_uuid'];
                $response[$key]['class_schedule_uuid'] = $value['class_schedule_uuid'];
                $response[$key]['title'] = $value['classes']['name'];
                $response[$key]['address'] = $value['classes']['address'];
                $response[$key]['lat'] = $value['classes']['lat'];
                $response[$key]['lng'] = $value['classes']['lng'];
                $response[$key]['package_uuid'] = AppointmentResponseHelper::getCustomerUpcomingClassPackageUuid($value, $customer);
                $response[$key]['class_image'] = !empty($value['classes']['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['class_images'] . $value['classes']['image'] : null;
                $response[$key]['date'] = $value['class_date'];
                $response[$key]['start_time'] = $from_time_local_conversion;
                $response[$key]['end_time'] = $to_time_local_conversion;
                $response[$key]['datetime'] = $value['class_date'] . ' ' . $from_time_local_conversion;
                $response[$key]['price'] = !empty($value['classes']['price']) ? (double) CommonHelper::getConvertedCurrency($value['classes']['price'], $value['classes']['currency'], $value['classes']['freelancer']['default_currency']) : 0;
                $response[$key]['type'] = 'class';
                $response[$key]['status'] = $value['status'];
                $response[$key]['online_link'] = $value['online_link'];
                $response[$key]['service'] = self::appointmentServiceResponse((!empty($value['classes']['freelance_category']) ? $value['classes']['freelance_category'] : []), $value['classes']['currency'], $value['classes']['freelancer']['default_currency']);
                $response[$key]['freelancer'] = self::appointmentFreelancerResponse(!empty($value['classes']['freelancer']) ? $value['classes']['freelancer'] : []);
                $response[$key]['customer'] = self::appointmentCustomerResponse([]);
                $response[$key]['customer_name'] = null;
                $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse();
                if (!empty($customer)) {
                    $response[$key]['customer'] = self::appointmentCustomerResponse($customer);
                    $response[$key]['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                    $response[$key]['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($customer['profile_image']);
                }
            }
            return $response;
        }
    }

}

?>
