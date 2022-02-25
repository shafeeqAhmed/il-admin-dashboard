<?php

namespace App\Helpers;

use App\MoyasarWebForm;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Appointment;
use App\Classes;
use App\BlockedTime;
use App\Schedule;

Class MultipleAppointmentHelper {
    /*
      |--------------------------------------------------------------------------
      | AppointmentHelper that contains appointment related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use appointment processes
      |
     */

    /**
     * Description of AppointmentHelper
     *
     * @author ILSA Interactive
     */
    public static function addMultipleAppointment($inputs = []) {

        if (isset($inputs['schedule']) && !empty($inputs['schedule'])) {

            $complete_array = [];
            $schedules = $inputs['schedule'];

            $inputs['package_uuid'] = $inputs['schedule'][0]['package_uuid'];
            unset($inputs['schedule']);

//            if (isset($schedules[0]['resource_path']) && !empty($schedules[0]['resource_path'])) {
//                $trans_inputs = ['resource_path' => $schedules[0]['resource_path'], 'currency' => $inputs['currency']];
//                $transaction = HyperpayHelper::checkTransactionStatus($trans_inputs);
//                if (!$transaction['success']) {
//                    return CommonHelper::jsonErrorResponse($transaction['message']);
//                }
//                $inputs['transaction_id'] = $transaction['transaction_id'];
//                $inputs['payment_details'] = $transaction['payment_details'];
//                if (!empty($transaction['registration_id'])) {
//                    $inputs['card_info'] = $transaction['payment_details']->card;
//                    $save_registaration_id = PaymentHelper::saveRegistrationId($inputs, $transaction['registration_id']);
//                    if (!$save_registaration_id['success']) {
//                        return CommonHelper::jsonErrorResponse($save_registaration_id['message']);
//                    }
//                }
//            }
//
//            elseif ( !empty($schedules[0]['source_id']) ){
//                $payment = MoyasarHelper::getPayment($schedules[0]['source_id']);
//                if (!$payment['success'] || empty($payment = $payment['payment'])):
//                    return CommonHelper::jsonErrorResponse('Invalid source id.');
//                endif;
//                if ($payment->status != 'paid'){
//                    return CommonHelper::jsonErrorResponse('Payment is not paid.');
//                }
//                $webForm = MoyasarWebForm::where([
//                    'profile_uuid' => $inputs['logged_in_uuid'],
//                    'moyasar_web_form_uuid' => $schedules[0]['moyasar_web_form_uuid'] ?? '',
//                ])->first();
//                if (empty($webForm)):
//                    return CommonHelper::jsonErrorResponse('Web Form not found for logged in user.');
//                endif;
//                if ($webForm->amount != $payment->amount){
//                    return CommonHelper::jsonErrorResponse('Payment amount is not same as web form.');
//                }
//                $webForm->payment_id = $payment->id;
//                $webForm->status = 'paid';
//                $webForm->save();
//                $inputs['transaction_id'] = $payment->id;
//                $inputs['payment_details'] = $payment;
//                $inputs['payment_processor'] = 'moyasar';
////                $inputs['paid_amount'] = $payment->amount / 100;
//            }

            foreach ($schedules as $key => $schedule) {
                $schedule['datetime'] = $schedule['date'] . ' ' . $schedule['start_time'];

                $schedules[$key] = $schedule;
            }

            usort($schedules, function($a, $b) {
                $t1 = strtotime($a['datetime']);
                $t2 = strtotime($b['datetime']);
                return $t1 - $t2;
            });

            foreach ($schedules as $key => $schedule) {
                $schedule['session_number'] = (int) ($key + 1);
                $schedule['total_session'] = count($schedules);
                $schedules[$key] = $schedule;
            }

            foreach ($schedules as $key => $schedule) {
                $new_array = $inputs + $schedule;

                $freelancer_appointment_data = AppointmentDataHelper::makeFreelancerAppointmentArray($new_array);

                $freelancer_appointment_data['location_type'] = AppointmentDataHelper::processAppointmentLocationType($new_array);

                if (!empty($freelancer_appointment_data['is_online']) && $freelancer_appointment_data['is_online']) {
                    $validation = Validator::make($freelancer_appointment_data, AppointmentValidationHelper::freelancerAddOnlineMultipleAppointmentRules()['rules'], AppointmentValidationHelper::freelancerAddOnlineMultipleAppointmentRules()['message_' . strtolower($inputs['lang'])]);
                } else {
                    $validation = Validator::make($freelancer_appointment_data, AppointmentValidationHelper::freelancerAddMultipleAppointmentRules()['rules'], AppointmentValidationHelper::freelancerAddMultipleAppointmentRules()['message_' . strtolower($inputs['lang'])]);
                }
                if ($validation->fails()) {
                    return CommonHelper::jsonErrorResponse($validation->errors()->first() . ' in ' . CommonHelper::getEnglishInteger($key + 1) . ' block');
                }
                $start_time = FreelancerschedulerHelper::setAmPmInTime(CommonHelper::convertTimeToTimezone($schedule['start_time'], 'UTC', $new_array['local_timezone']));
                $end_time = FreelancerschedulerHelper::setAmPmInTime(CommonHelper::convertTimeToTimezone($schedule['end_time'], 'UTC', $new_array['local_timezone']));

                $appointment_array = [
                    'login_user_type' => $inputs['login_user_type'],
                    'logged_in_uuid' => $freelancer_appointment_data['logged_in_uuid'],
                    'local_timezone' => $freelancer_appointment_data['local_timezone'],
                    'customer_id' => $freelancer_appointment_data['customer_id'],
                    'freelancer_id' =>$freelancer_appointment_data['freelancer_id'],
                    'date' => $freelancer_appointment_data['appointment_date'],
                    'from_time' => $freelancer_appointment_data['from_time'],
                    'to_time' => $freelancer_appointment_data['to_time']
                ];

                $schedule_check = AppointmentHelper::checkFreelancerSchedule($appointment_array);

                if (!$schedule_check) {
                    $exceeding_time = $start_time . ' - ' . $end_time . ' time slot is exceeding schedule limit';
                    return CommonHelper::jsonErrorResponse($exceeding_time);
                }

                $check_customer_time_available = CustomerChecksHelper::checkCustomerExistingSession($appointment_array);
                if (!$check_customer_time_available['success']) {
                    return CommonHelper::jsonErrorResponse($check_customer_time_available['message']);
                }
                $result = self::continueUpdateAppointmentProcess($new_array, $freelancer_appointment_data, $appointment_array, $start_time, $end_time);
                // unset is online param coz is_online not exist in appointment table and this column create error for inset function
//                unset($freelancer_appointment_data['is_online']);
                $complete_array[$key] = $freelancer_appointment_data;
                if (empty($result->original['success'])) {
                    return $result;
                }

            }



            return self::freelancerAddMultipleAppointmentProcess($inputs, $complete_array);
        }
    }

    public static function continueUpdateAppointmentProcess($inputs, $freelancer_appointment_data, $appointment_array, $start_time, $end_time) {
        $appointment_check = AppointmentHelper::checkFreelancerScheduledAppointment($appointment_array);
        if ($appointment_check) {
            $overlapping = $start_time . ' - ' . $end_time . ' time slot is overlaping with an existing appointment';
            return CommonHelper::jsonErrorResponse($overlapping);
        }
        $blocked_time_check = AppointmentHelper::checkFreelancerBlockedTiming($appointment_array);
        if ($blocked_time_check) {
            $block = $start_time . ' - ' . $end_time . ' time slot is overlaping with blocked timings';
            return CommonHelper::jsonErrorResponse($block);
        }
        $class_check = AppointmentHelper::checkFreelancerClass($appointment_array);
        if ($class_check) {
            $class_error = $start_time . ' - ' . $end_time . ' time slot is overlaping with existing class';
            return CommonHelper::jsonErrorResponse($class_error);
        }
        return CommonHelper::jsonSuccessResponse();
    }

    public static function freelancerAddMultipleAppointmentProcess($inputs, $freelancer_appointment_data) {
        $save_appointment = [];
        $purchased_package_uuid = UuidHelper::generateUniqueUUID('appointments', 'purchased_package_uuid');
        foreach ($freelancer_appointment_data as $key => $appointment) {
            $appointment['purchased_package_uuid'] = $purchased_package_uuid;

            $save_appointment[$key] = Appointment::saveAppointment($appointment);

            $save_appointment[$key]['login_user_type'] = $inputs['login_user_type'];
            $save_appointment[$key]['exchange_rate'] = config('general.globals.' . $inputs['currency']);
            $save_appointment[$key]['to_currency'] = $inputs['currency'];
//            $save_appointment[$key]['payment_brand'] = !empty($inputs['payment_details']->paymentBrand) ? $inputs['payment_details']->paymentBrand : null;
            $save_appointment[$key]['payment_brand'] = !empty($inputs['payment_details']->source) && !empty($inputs['payment_details']->source->type) ? $inputs['payment_details']->source->type : null;
            $save_appointment[$key]['moyasar_fee'] = !empty($inputs['payment_details']->fee) ? $inputs['payment_details']->fee : 0;
            $save_appointment[$key]['lang'] = $inputs['lang'];
        }

        if (!$save_appointment) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_appointment_error']);
        }

        $appointment_uuids['appointment_uuid'] = $save_appointment[0]['appointment_uuid'];
        $inputs['purchase_time'] = $save_appointment[0]['created_at'];
        $freelancer_appointment_data['lang'] = $inputs['lang'];

        $inputs['freelancer_id'] = CommonHelper::getRecordByUuid( 'freelancers','freelancer_uuid',$inputs['freelancer_uuid']);
        $inputs['customer_id'] = CommonHelper::getRecordByUuid('customers','customer_uuid',$inputs['customer_uuid']);;


        $client_array = ['customer_id' => $inputs['customer_id'], 'freelancer_id' => $inputs['freelancer_id'], 'lang' => $inputs['lang']];

        $save_appointment_client = ClientHelper::addClient($client_array);
        if (!$save_appointment_client['success']) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse($save_appointment_client['message']);
        }
        $client_array = $client_array + $appointment_uuids;
//        foreach ($save_appointment as $appointments) {
//            $save_transaction = TransactionHelper::saveAppointmentTransaction($appointments);
//            if (empty($save_transaction['success'])) {
//                DB::rollBack();
//                return CommonHelper::jsonErrorResponse($save_transaction['message']);
//            }
//            /*$saveTransactionPaymentDue = TransactionHelper::saveTransactionPaymentDue($save_transaction->toArray(), 'appointment', $appointments['appointment_date']);
//            if (!$saveTransactionPaymentDue) {
//                DB::rollBack();
//                return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['add_transaction_request_due_error']);
//            }*/
//        }
        $inputs['notification_appointment_type'] = "multiple";

        ProcessNotificationHelper::sendAppointmentNotification($client_array, $inputs);
        $temp = [];
        $temp['purchased_package_uuid'] = $purchased_package_uuid;
        $temp['status'] = "pending";
        $temp['package_uuid'] = $inputs['package_uuid'];

        $check = AppointmentHelper::prepareAppointmentEmailData($temp, "package_appointments_status_update", "New Package Request", "package", $inputs);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(AppointmentValidationHelper::freelancerAddAppointmentRules()['message_' . strtolower($inputs['lang'])]['save_appointment_success']);
    }

}

?>
