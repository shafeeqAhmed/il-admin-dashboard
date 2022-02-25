<?php

namespace App\Helpers;

use App\UserDevice;
use App\Freelancer;
use App\Customer;
use App\Notification;
use App\NotificationSetting;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;

/*
  All methods related to user notifications will be here
 */

class ProcessNotificationHelper {

    public static function processFreelancer($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['freelancer_uuid'] = $data['freelancer_uuid'];
            $response['freelancer_id'] = $data['id'];
            $response['user_id'] = $data['user_id'];
            $response['first_name'] = (!empty($data['first_name'])) ? $data['first_name'] : "";
            $response['last_name'] = (!empty($data['last_name'])) ? $data['last_name'] : "";
            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
//            $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['profile_image']);
        }
        return $response;
    }

    public static function processCustomer($data = []) {
        $response = [];

        if (!empty($data)) {
            $response['customer_uuid'] = $data['customer_uuid'];
            $response['customer_id'] = $data['id'];
            $response['user_id'] = $data['user_id'];
            $response['first_name'] = (!empty($data['user']['first_name'])) ? $data['user']['first_name'] : "";
            $response['last_name'] = (!empty($data['user']['last_name'])) ? $data['user']['last_name'] : "";
//            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $data['profile_image'] : null;
//            $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['profile_image']);
            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $data['profile_image'] : null;
        }
        return $response;
    }

    public static function processChatFreelancer($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['uuid'] = !empty($data['freelancer_uuid']) ? $data['freelancer_uuid'] : "";
            $response['id'] = !empty($data['freelancer_uuid']) ? $data['freelancer_uuid'] : "";
            $response['user_id'] = !empty($data['user_id']) ? $data['user_id'] : "";
//            $response['first_name'] = (!empty($data['first_name'])) ? $data['first_name'] : "";
//            $response['last_name'] = (!empty($data['last_name'])) ? $data['last_name'] : "";
            $response['name'] = $data["first_name"] . $data['last_name'];
            $response['type'] = "freelancer";
//            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
//            $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['profile_image']);
            $response['image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
        }
        return $response;
    }

    public static function processChatCustomer($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['uuid'] = !empty($data['user_uuid']) ? $data['user_uuid'] : "";
            $response['id'] = !empty($data['user_uuid']) ? $data['user_uuid'] : "";
            $response['user_id'] = !empty($data['id']) ? $data['id'] : "";
            $response['type'] = "customer";
            $response['name'] = $data["first_name"] . $data['last_name'];
            $response['image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['customer_profile_image'] . $data['profile_image'] : null;
//            $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['profile_image']);
        }
        return $response;
    }

    public static function sendFollowerNotification($inputs = [], $follower = [], $notificationType = 'new_follower') {

        $receiver_data = self::processReceiver($inputs);
        $sender_data = self::processSender($inputs);
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' has started following you',
            'save_message' => ' has started following you',
            'data' => $data,
            'follow_uuid' => $follower['follow_uuid'],
        ];
//        if (!empty($receiver_data['device_token'])) {
//        return PushNotificationHelper::send_voip_notification_to_user($receiver_data['voip_device_token'], $messageData);
        $notification_inputs = self::prepareInputs($messageData);
        $check = self::checkAndUpdateNotification($notification_inputs);
        $save_notification = Notification::addNotification($notification_inputs);
        $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $receiver_data['receiver']['freelancer_uuid'], 'new_follower');
        if (!empty($check_notification_setting)) {
//                return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
            return PushNotificationHelper::send_notification_to_user_devices($receiver_data['receiver']['freelancer_uuid'], $messageData);
        }
//            return PushNotificationHelper::send_notification_to_user("1043cb44ca36801c17aec373ff28bd0c7bfcb0ad52ccf3ce0e94928806d1d09b", $messageData);
//        }
    }

    public static function checkAndUpdateNotification($notification_inputs = []) {
        $check = Notification::checkNotification($notification_inputs);
        if (!empty($check)) {
            $update = Notification::updateNotification($notification_inputs, ['is_archive' => 1]);
        }
        return true;
    }

    public static function processSender($inputs = []) {
        $sender = ['sender' => [], 'device_token' => ''];
        $profile = Customer::getSingleCustomer('id', $inputs['follower_id']);
        if (!empty($profile)) {
            $sender['sender'] = self::processCustomer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['follower_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function processReceiver($inputs = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        $profile = Freelancer::checkFreelancer('id', $inputs['following_id']);
        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['following_id']);
            $receiver['device_token'] = $device['device_token'];
        }
        return $receiver;
    }

    public static function prepareInputs($messageData = []) {
        $notification_inputs = [];
//        if (!empty($data)) {
        if (isset($messageData['data']['receiver']['freelancer_id'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
            $notification_inputs['freelancer_receiver_id'] = $messageData['data']['receiver']['freelancer_id'];
        } if (isset($messageData['data']['sender']['customer_id'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
        }

        $notification_inputs['message'] = $messageData['save_message'];
        $notification_inputs['notification_type'] = $messageData['type'];
        $notification_inputs['is_read'] = 0;
        if ($messageData['type'] == "new_follower") {
            $notification_inputs['uuid'] = $messageData['follow_uuid'];
        }
        if ($messageData['type'] == "new_rating") {
            $notification_inputs['uuid'] = $messageData['review_uuid'];
        }
        if ($messageData['type'] == "new_like") {
            $notification_inputs['uuid'] = $messageData['post_uuid'];
        }

//        }
        return $notification_inputs;
    }

    public static function sendSubscriberNotification($inputs = [], $subscription = [], $notificationType = 'new_subscriber') {

        $receiver_data = self::processSubscriptionReceiver($inputs);
        $sender_data = self::processSubscriptionSender($inputs);
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $data['subscription']['subscription_uuid'] = $subscription['subscription_uuid'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' has bought a subscription',
            'save_message' => 'has bought a subscription',
            'data' => $data,
            'subscription_uuid' => $subscription['subscription_uuid'],
        ];
        $notification_inputs = self::prepareSubscriptionInputs($messageData);
        $save_notification = Notification::addNotification($notification_inputs);
        return PushNotificationHelper::send_notification_to_user_devices($inputs['subscribed_uuid'], $messageData);
//        if (!empty($receiver_devices)) {
////        return PushNotificationHelper::send_voip_notification_to_user($receiver_data['voip_device_token'], $messageData);
//
////            $check = self::checkAndUpdateNotification($notification_inputs);
//
////            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function updateSubscriptionNotification($inputs = [], $notificationType = 'subscription_update') {
        $receiver_data = self::processSubscriptionReceiver($inputs);
        $sender_data = self::processSubscriptionSender($inputs);
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' ' . $inputs['message'],
            'save_message' => $inputs['message'],
            'data' => $data,
            'subscription_uuid' => $inputs['subscription_uuid'],
        ];
        $notification_inputs = self::prepareSubscriptionInputs($messageData);
        $save_notification = Notification::addNotification($notification_inputs);
        return PushNotificationHelper::send_notification_to_user_devices($inputs['subscribed_uuid'], $messageData);
//        if (!empty($receiver_data['device_token'])) {
////        return PushNotificationHelper::send_voip_notification_to_user($receiver_data['voip_device_token'], $messageData);
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function processSubscriptionSender($inputs = []) {
        $sender = ['sender' => [], 'device_token' => ''];

        $profile = Customer::getSingleCustomer('customer_uuid', $inputs['subscriber_uuid']);

        if (empty($profile)) {
            $profile = Freelancer::checkFreelancer('freelancer_uuid', $inputs['subscriber_uuid']);
        }
        if (!empty($profile['customer_uuid'])) {
            $sender['sender'] = self::processCustomer($profile);
        }
        if (!empty($profile['freelancer_uuid'])) {
            $sender['sender'] = self::processFreelancer($profile);
        }
        $device = UserDevice::getUserDevice('profile_uuid', $inputs['subscriber_uuid']);
        if (!empty($device)) {
            $sender['device_token'] = $device['device_token'];
        }

        return $sender;
    }

    public static function processSubscriptionReceiver($inputs = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];

        $profile = Freelancer::checkFreelancer('freelancer_uuid', $inputs['subscribed_uuid']);
        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('profile_uuid', $inputs['subscribed_uuid']);
            $receiver['device_token'] = $device['device_token'];
        }
        return $receiver;
    }

    public static function prepareSubscriptionInputs($messageData = []) {
        $notification_inputs = [];

        if (isset($messageData['data']['receiver']['freelancer_uuid'])) {
            $notification_inputs['receiver_uuid'] = $messageData['data']['receiver']['freelancer_uuid'];
        } if (isset($messageData['data']['sender']['customer_uuid'])) {
            $notification_inputs['sender_uuid'] = $messageData['data']['sender']['customer_uuid'];
        }
        if (isset($messageData['data']['sender']['freelancer_uuid'])) {
            $notification_inputs['sender_uuid'] = $messageData['data']['sender']['freelancer_uuid'];
        }
        $notification_inputs['uuid'] = $messageData['subscription_uuid'];
        $notification_inputs['message'] = $messageData['save_message'];
        $notification_inputs['notification_type'] = $messageData['type'];
        $notification_inputs['is_read'] = 0;

        return $notification_inputs;
    }

    public static function sendClassBookingNotification($data = [], $inputs = [], $notificationType = 'new_class_booking') {

        if ($inputs['login_user_type'] == "customer") {
            $sender_data = self::processCustomerSender($data);
            $receiver_data = self::processFreelancerReceiver($data);
            return self::processClassBookingNotificationData($sender_data, $receiver_data, $data, $inputs, $notificationType = 'new_class_booking');
        } elseif ($inputs['login_user_type'] == "freelancer") {
            $sender_data = self::processFreelancerSender($data);
            $receiver_data = self::processCustomerReceiver($data);
            return self::processClassBookingNotificationData($sender_data, $receiver_data, $data, $inputs, $notificationType = 'new_class_booking');
        }
        return true;
    }

    public static function processClassBookingNotificationData($sender_data = [], $receiver_data = [], $notification_data = [], $inputs = [], $notificationType = 'new_class_booking') {
        $data = [];
        $data['sender'] = [];
        $data['receiver'] = [];
        if (!empty($sender_data['sender'])) {
            $data['sender'] = $sender_data['sender'];
        }
        if (!empty($receiver_data['receiver'])) {
            $data['receiver'] = $receiver_data['receiver'];
        }
        $classUuid = CommonHelper::getRecordByUuid('classes', 'id', $notification_data['class_id'], 'class_uuid');
        $scheduleUuid = CommonHelper::getRecordByUuid('class_schedules', 'id', $notification_data['class_schedule_id'], 'class_schedule_uuid');
        $data['class']['class_uuid'] = $classUuid;
        $data['class']['class_schedule_uuid'] = $scheduleUuid;
        $data['class']['class_date'] = $notification_data['class_date'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' has booked on a class',
            'save_message' => ' has booked on a class',
            'data' => $data,
            'class_uuid' => $classUuid,
            'class_schedule_uuid' => $scheduleUuid,
            'class_date' => $notification_data['class_date'],
        ];

        $notification_inputs = self::prepareClassBookingNotificationInputs($messageData);

        $save_notification = Notification::addNotification($notification_inputs);
        return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['freelancer_uuid'] : $data['receiver']['customer_uuid'], $messageData);
//        if (!empty($receiver_data['device_token'])) {
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function sendRescheduledAppointmentNotification($data = [], $inputs = [], $notificationType = 'reschedule_appointment') {

        if ($inputs['login_user_type'] == "customer") {
            $sender_data = self::processRescheduledCustomer($data['appointment_customer']);
            $receiver_data = self::processRescheduledFreelancer($data['appointment_freelancer']);

            return self::processRescheduleAppointmentNotification($sender_data, $receiver_data, $data, $inputs, $notificationType = 'reschedule_appointment');
        } elseif ($inputs['login_user_type'] == "freelancer") {
            $sender_data = self::processRescheduleFreelancerSender($data['appointment_freelancer']);
            $receiver_data = self::processRescheduleCustomerReceiver($data['appointment_customer']);

            return self::processRescheduleAppointmentNotification($sender_data, $receiver_data, $data, $inputs, $notificationType = 'reschedule_appointment');
        }
    }

    public static function processRescheduleAppointmentNotification($sender_data = [], $receiver_data = [], $notification_data = [], $inputs = [], $notificationType = 'reschedule_appointment') {
        $data = [];
        $data['sender'] = [];
        $data['receiver'] = [];
        $check_notification_setting = [];
        if (!empty($sender_data['sender'])) {
            $data['sender'] = $sender_data['sender'];
        }
        if (!empty($receiver_data['receiver'])) {
            $data['receiver'] = $receiver_data['receiver'];
        }
        $data['appointment']['appointment_uuid'] = $notification_data['appointment_uuid'];
        $message = ($inputs['login_user_type'] == "freelancer") ? (' has rescheduled your booking') : (' has rescheduled their booking');
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . $message,
            'save_message' => $message,
            'data' => $data,
            'appointment_uuid' => $notification_data['appointment_uuid'],
        ];
        $notification_inputs = self::prepareAppointmentNotificationInputs($messageData);
        $save_notification = Notification::addNotification($notification_inputs);

        if ($inputs['login_user_type'] == "customer") {

            $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $data['receiver']['user_id'], 'new_appointment');
        } elseif ($inputs['login_user_type'] == "freelancer" && !empty($data['receiver']['customer_uuid'])) {

            $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $data['receiver']['user_id'], 'new_appointment');

//        } if (!empty($receiver_data['device_token']) && !empty($check_notification_setting)) {
        }

        if (!empty($check_notification_setting)) {

            return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['user_id']) ? $data['receiver']['user_id'] : $data['receiver']['user_id'], $messageData);
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
        }
    }

    public static function processRescheduleFreelancerSender($profile = []) {
        $sender = ['sender' => [], 'device_token' => ''];
        if (!empty($profile)) {
            $sender['sender'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('user_id', $profile['user_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function processRescheduledFreelancer($profile = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('user_id', $profile['user_id']);
            if (!empty($device)) {
                $receiver['device_token'] = $device['device_token'];
            }
        }
        return $receiver;
    }

    public static function processRescheduleCustomerReceiver($profile = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        if (!empty($profile)) {
            $receiver['receiver'] = self::processCustomer($profile);
            $device = UserDevice::getUserDevice('user_id', $profile['user_id']);
            if (!empty($device)) {
                $receiver['device_token'] = $device['device_token'];
            }
        }
        return $receiver;
    }

    public static function processRescheduledCustomer($profile = []) {

        $sender = ['sender' => [], 'device_token' => ''];
        if (!empty($profile)) {
            $sender['sender'] = self::processCustomer($profile);
            $device = UserDevice::getUserDevice('user_id', $profile['user_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function sendAppointmentNotification($data = [], $inputs = [], $notificationType = 'new_appointment') {
        if ($inputs['login_user_type'] == "customer") {
            $sender_data = self::processCustomerSender($data);

            $receiver_data = self::processFreelancerReceiver($data);

            return self::processAppointmentNotificationData($sender_data, $receiver_data, $data, $inputs, $notificationType = 'new_appointment');
        } elseif ($inputs['login_user_type'] == "freelancer") {

            $sender_data = self::processFreelancerSender($data);

            $receiver_data = self::processCustomerReceiver($data);
            return self::processAppointmentNotificationData($sender_data, $receiver_data, $data, $inputs, $notificationType = 'new_appointment');
        } elseif ($inputs['login_user_type'] != "freelancer" || $inputs['login_user_type'] != "customer") {
            return self::processAdminAsSender($data, $inputs, $notificationType = 'new_appointment');
        }
    }

    public static function processAppointmentNotificationData($sender_data = [], $receiver_data = [], $notification_data = [], $inputs = [], $notificationType = 'new_appointment') {


        $inputs['freelanceId'] = CommonHelper::getRecordByUuid('freelancers', 'freelancer_uuid', $inputs['freelancer_uuid'], 'id');
        $inputs['customerId'] = CommonHelper::getRecordByUuid('customers', 'customer_uuid', $inputs['customer_uuid'], 'id');

        $data = [];
        $data['sender'] = [];
        $data['receiver'] = [];
        $check_notification_setting = [];
        if (!empty($sender_data['sender'])) {
            $data['sender'] = $sender_data['sender'];
        }
        if (!empty($receiver_data['receiver'])) {
            $data['receiver'] = $receiver_data['receiver'];
        }

        if (isset($inputs['notification_appointment_type']) && $inputs['notification_appointment_type'] == "multiple") {
            $data['appointment']['package_uuid'] = $inputs['package_uuid'];
            $data['appointment']['purchase_time'] = $inputs['purchase_time'];
            $data['appointment']['appointment_uuid'] = $notification_data['appointment_uuid'];
            $messageData = [
                'type' => "appointment_package",
                'message' => $sender_data['sender']['first_name'] . ' has booked a package',
                'save_message' => ' has booked a package',
                'data' => $data,
                'appointment_uuid' => $notification_data['appointment_uuid'],
                'purchase_time' => $inputs['purchase_time'],
                'package_uuid' => $inputs['package_uuid'],
            ];
        } else {

            $data['appointment'] = self::prepareAppointmentData($notification_data);

            $messageData = [
                'type' => $notificationType,
                'message' => $sender_data['sender']['first_name'] . ' has sent a new booking request',
                'save_message' => ' has sent a new booking request',
                'data' => $data,
                'appointment_uuid' => $notification_data['appointment_uuid'],
            ];
        }

        $notification_inputs = self::prepareAppointmentNotificationInputs($messageData);

        $save_notification = Notification::addNotification($notification_inputs);

        if ($inputs['login_user_type'] == "customer") {
            $user = Freelancer::find($inputs['freelanceId'])->first();

            // $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $inputs['freelanceId'], 'new_appointment');
            $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $user->user_id, 'new_appointment');
        } elseif ($inputs['login_user_type'] == "freelancer") {
            $user = Customer::find($inputs['customerId'])->first();
            //$check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $inputs['customerId'], 'new_appointment');
            $check_notification_setting = NotificationSetting::getSettingsWithType('user_id', $user->user_id, 'new_appointment');
//        } if (!empty($receiver_data['device_token']) && !empty($check_notification_setting)) {
        }

        if (!empty($check_notification_setting)) {
            $messageData['receiver_id'] = !empty($data['receiver']['user_id']) ? $data['receiver']['user_id'] : null;
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
            return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['user_id']) ? $data['receiver']['user_id'] : $data['receiver']['user_id'], $messageData);
        }
    }

    public static function prepareAppointmentData($data = []) {
        $appointment = [];
        if (!empty($data)) {
            $appointment['appointment_uuid'] = $data['appointment_uuid'];
        }
        return $appointment;
    }

    public static function processAdminAsSender($inputs = [], $inputs_data = [], $notificationType = 'new_appointment') {
        $receiver_data[0] = self::processFreelancerReceiver($inputs);
        $receiver_data[1] = self::processCustomerReceiver($inputs);
        $sender_data = self::processAdminSender($inputs);
        foreach ($receiver_data as $key => $receiver) {
            $data['sender'] = $sender_data['sender'];
            $data['receiver'] = $receiver_data;
            $messageData = [
                'type' => $notificationType,
                'message' => $sender_data['sender']['first_name'] . ' has created a new appointment',
                'save_message' => ' has created a new appointment',
                'data' => $data,
                'appointment_uuid' => $inputs['appointment_uuid'],
            ];

            $notification_inputs = self::prepareAppointmentInsertionInputs($messageData);
            $insert_notification = Notification::insertNotification($notification_inputs);
            return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['freelancer_uuid'] : $data['receiver']['customer_uuid'], $messageData);
//            if (!empty($receiver['device_token'])) {
//                return PushNotificationHelper::send_notification_to_user($receiver['device_token'], $messageData);
//            }
        }
    }

    public static function processAdminSender($inputs = []) {
        $sender = ['sender' => [], 'device_token' => ''];
        $sender['sender'] = self::processAdminSenderInputs();
        $sender['device_token'] = "1043cb44ca36801c17aec373ff28bd0c7bfcb0ad52ccf3ce0e94928806d1d09b";
        return $sender;
    }

    public static function processAdminSenderInputs() {
        $response = [];
        $response['admin_uuid'] = "1099fde0-d690-11e8-8356-33b02efa9k0g";
        $response['first_name'] = "Admin";
        $response['last_name'] = "Circle";
        $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse();
        return $response;
    }

    public static function prepareAppointmentInsertionInputs($messageData = []) {
        $notification_inputs = [];
        $notification_inputs[0]['notification_uuid'] = self::getUniqueNotificationUUID();
        $notification_inputs[0]['receiver_uuid'] = $messageData['data']['receiver'][0]['receiver']['freelancer_uuid'];
        $notification_inputs[0]['sender_uuid'] = $messageData['data']['sender']['admin_uuid'];
        $notification_inputs[0]['uuid'] = $messageData['appointment_uuid'];
        $notification_inputs[0]['message'] = $messageData['save_message'];
        $notification_inputs[0]['notification_type'] = $messageData['type'];
        $notification_inputs[0]['is_read'] = 0;

        $notification_inputs[1]['notification_uuid'] = self::getUniqueNotificationUUID();
        $notification_inputs[1]['receiver_uuid'] = $messageData['data']['receiver'][1]['receiver']['customer_uuid'];
        $notification_inputs[1]['sender_uuid'] = $messageData['data']['sender']['admin_uuid'];
        $notification_inputs[1]['uuid'] = $messageData['appointment_uuid'];
        $notification_inputs[1]['message'] = $messageData['save_message'];
        $notification_inputs[1]['notification_type'] = $messageData['type'];
        $notification_inputs[1]['is_read'] = 0;

        return $notification_inputs;
    }

    public static function getUniqueNotificationUUID() {
        $data['notification_uuid'] = Uuid::uuid4()->toString();
        $validation = Validator::make($data, NotificationValidationHelper::$add_notification_uuid_rules);
        if ($validation->fails()) {
            //$this->getUniquePostImageUUID();
        }
        return $data['notification_uuid'];
    }

    public static function processCustomerSender($inputs = []) {

        $sender = ['sender' => [], 'device_token' => ''];
        $profile = Customer::getSingleCustomer('id', $inputs['customer_id']);
        if (!empty($profile)) {
            $sender['sender'] = self::processCustomer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['customer_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function processFreelancerSender($inputs = []) {

        $sender = ['sender' => [], 'device_token' => ''];
        $profile = Freelancer::checkFreelancer('id', $inputs['freelancer_id']);

        if (!empty($profile)) {
            $sender['sender'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['freelancer_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function processFreelancerReceiver($inputs = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        $profile = Freelancer::checkFreelancer('id', $inputs['freelancer_id']);

        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['freelancer_id']);
            if (!empty($device)) {
                $receiver['device_token'] = $device['device_token'];
            }
        }
        return $receiver;
    }

    public static function processCustomerReceiver($inputs = []) {

        $receiver = ['receiver' => [], 'device_token' => ''];
        $profile = [];
        if (!empty($inputs['customer_id'])) {
            $profile = Customer::getSingleCustomer('id', $inputs['customer_id']);
        }

        if (!empty($profile)) {
            $receiver['receiver'] = self::processCustomer($profile);
            $device = UserDevice::getUserDevice('user_id', $inputs['customer_id']);
            if (!empty($device)) {
                $receiver['device_token'] = $device['device_token'];
            }
        }
        return $receiver;
    }

    public static function prepareAppointmentNotificationInputs($messageData = []) {

        $notification_inputs = [];

        if (isset($messageData['data']['receiver']['freelancer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
            $notification_inputs['freelancer_receiver_id'] = $messageData['data']['receiver']['freelancer_id'];
            //$notification_inputs['receiver_user'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['receiver']['customer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
            //$notification_inputs['receiver_user'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['sender']['customer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
            //$notification_inputs['sender_user'] = $messageData['data']['sender']['user_id'];
        }
        if (isset($messageData['data']['sender']['freelancer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
            $notification_inputs['freelancer_sender_id'] = $messageData['data']['sender']['freelancer_id'];
            //$notification_inputs['sender_user'] = $messageData['data']['sender']['user_id'];
        }

        if (isset($messageData['data']['sender']['admin_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['admin_uuid'];
        }
        $notification_inputs['uuid'] = $messageData['appointment_uuid'];
        $notification_inputs['purchase_time'] = isset($messageData['purchase_time']) ? $messageData['purchase_time'] : null;
        //$notification_inputs['package_uuid'] = isset($messageData['package_uuid']) ? $messageData['package_uuid'] : null;
        $notification_inputs['message'] = $messageData['save_message'];
        $notification_inputs['notification_type'] = $messageData['type'];
        $notification_inputs['is_read'] = 0;

        return $notification_inputs;
    }

    public static function sendRatingNotification($inputs = [], $review = [], $notificationType = 'new_rating') {
        $receiver_data = self::processRatingReceiver($inputs, $review);
        $sender_data = self::processRatingSender($inputs, $review);
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $data['review']['review_uuid'] = $review['review_uuid'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' has sent a review',
            'save_message' => ' has sent a review',
//            'save_message' => ' has rated your ' . $inputs['type'],
            'data' => $data,
            'review_uuid' => $review['review_uuid'],
        ];

//        if (!empty($receiver_data['device_token'])) {
        $notification_inputs = self::prepareInputs($messageData);
//            $check = self::checkAndUpdateNotification($notification_inputs);
        $save_notification = Notification::addNotification($notification_inputs);
        return PushNotificationHelper::send_notification_to_user_devices($data['receiver']['user_id'], $messageData);
//        return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function processRatingSender($inputs = [], $review = []) {
        $sender = ['sender' => [], 'device_token' => ''];
        $profile = Customer::getSingleCustomer('customer_uuid', $inputs['customer_uuid']);
        if (!empty($profile)) {
            $sender['sender'] = self::processCustomer($profile);
//            $device = UserDevice::getUserDevice('profile_id', $review['reviewer_id']);
//            if (!empty($device)) {
//                $sender['device_token'] = $device['device_token'];
//            }
        }
        return $sender;
    }

    public static function processRatingReceiver($inputs = [], $review = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        $profile = Freelancer::checkFreelancer('freelancer_uuid', $inputs['freelancer_uuid']);
        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
//            $device = UserDevice::getUserDevice('profile_id', $review['reviewed_id']);
//            $receiver['device_token'] = $device['device_token'];
        }
        return $receiver;
    }

    public static function prepareClassBookingNotificationInputs($messageData = []) {
        $notification_inputs = [];

        if (isset($messageData['data']['receiver']['freelancer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['receiver']['customer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['sender']['customer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
        }
        if (isset($messageData['data']['sender']['freelancer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
        }
        $notification_inputs['uuid'] = $messageData['class_uuid'];
        $notification_inputs['date'] = !empty($messageData['class_date']) ? $messageData['class_date'] : null;
        $notification_inputs['message'] = $messageData['save_message'];
        $notification_inputs['class_schedule_uuid'] = $messageData['class_schedule_uuid'];
        $notification_inputs['notification_type'] = $messageData['type'];
        $notification_inputs['is_read'] = 0;

        return $notification_inputs;
    }

    public static function sendLikeNotification($inputs = [], $like = [], $notificationType = 'new_like') {
        
        $receiver_data = self::processLikeReceiver($like);
        $sender_data = self::processLikeSender($like);
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $data['post'] = self::preparePostData($like);

        $messageData = [
            'type' => $notificationType,
            'message' => ' User has liked your post',
            'message' => $sender_data['sender']['first_name'] . 'liked your post',
            'save_message' => ' liked your post',
            'data' => $data,
//            'notification_send_type' => "mutable",
            'post_uuid' => $like['post_id'],
        ];
        $notification_inputs = self::prepareInputs($messageData);

        $check = self::checkAndUpdateNotification($notification_inputs);
        $save_notification = Notification::addNotification($notification_inputs);
        $messageData['receiver_id'] = !empty($data['receiver']['user_id']) ? $data['receiver']['user_id'] : null;

        //i change profile_uuid to with freelancer_uuid because profile_uuid now replace with user_id which is the PK of users table
        //actually we want freelancer record so i used freelancer_uuid here
//        return PushNotificationHelper::send_notification_to_user_devices($like['profile_uuid'], $messageData);
        return PushNotificationHelper::send_notification_to_user_devices($messageData['receiver_id'], $messageData);
//        if (!empty($receiver_data['device_token'])) {
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function preparePostData($data) {
        if (!empty($data)) {
            $post['post_id'] = $data['post_id'];
        }
        return ($post) ? $post : [];
    }

    public static function processLikeSender($inputs = []) {
        $sender = ['sender' => [], 'device_token' => ''];
        //liked_by_id is the PK of users table
        $profile = Customer::getSingleCustomer('user_id', $inputs['liked_by_id']);
        if (!empty($profile)) {
            $sender['sender'] = self::processCustomer($profile);
            // user_id in user_devices table is the FK of users table and $input['liked_by_id'] is also PK of users table
            $device = UserDevice::getUserDevice('user_id', $inputs['liked_by_id']);
            if (!empty($device)) {
                $sender['device_token'] = $device['device_token'];
            }
        }
        return $sender;
    }

    public static function processLikeReceiver($inputs = []) {
        $receiver = ['receiver' => [], 'device_token' => ''];
        //$input['user_id'] is a like table record and user_id is the parent_key of freelancers table record
        $profile = Freelancer::checkFreelancer('id', $inputs['user_id']);
        if (!empty($profile)) {
            $receiver['receiver'] = self::processFreelancer($profile);
            //$input is the record of like table
            //$input['user_id'] is the parent_key of freelancer who own the post
            $device = UserDevice::getUserDevice('user_id', $inputs['user_id']);
            if (!empty($device)) {
                $receiver['device_token'] = $device['device_token'];
            }
        }
        return $receiver;
    }

    public static function sendAdminAppointmentStatusNotification($inputs = [], $appointment = [], $notificationType = 'change_appointment_status') {
        $receivers = [];
        $receivers['customer'] = self::processCustomerReceiver([
                    'customer_uuid' => $appointment['customer_uuid'],
        ]);
        $receivers['freelancer'] = self::processFreelancerReceiver([
                    'freelancer_uuid' => $appointment['freelancer_uuid']
        ]);
        $sender_data = [
            'sender' => [
                'admin_uuid' => $inputs['logged_in_uuid'] ?? '',
                'first_name' => 'Admin',
                'last_name' => '',
                'profile_image' => null,
            ],
            'device_token' => ''
        ];

        foreach ($receivers as $userType => $receiver):
            $data = [];
            $data['sender'] = $sender_data['sender'];
            $data['receiver'] = $receiver['receiver'];
            $data['appointment']['appointment_uuid'] = $appointment['appointment_uuid'];
//            $message = ($userType == "freelancer") ? ('has ' . $inputs['status'] . ' your booking') : ('has ' . $inputs['status'] . ' their booking');
            $message = ' has ' . $inputs['status'] . ' your booking';
            $sender_name = $sender_data['sender']['first_name'] ?? '';
            $messageData = [
                'type' => $notificationType,
                'message' => $sender_name . $message,
                'save_message' => $message,
                'data' => $data,
                'appointment_uuid' => $appointment['appointment_uuid'],
            ];
            $notification_inputs = self::prepareAppointmentNotificationInputs($messageData);
            $save_notification = Notification::addNotification($notification_inputs);
            if (!$save_notification) {
                DB::rollBack();
                return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::changeAppointmentStatusRules()['message_' . strtolower($inputs['lang'])]['update_appointment_error']);
            }
//            var_dump(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['freelancer_uuid'] : $data['receiver']['customer_uuid'], $messageData);
            return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['freelancer_uuid'] : $data['receiver']['customer_uuid'], $messageData);
        endforeach;

//        die();
//        $check_notification_setting = NotificationSetting::getSettingsWithType('profile_uuid', $receiver_data['receiver']['freelancer_uuid'], $inputs['status']);
//        if (!empty($receiver_data['device_token'])) {
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function sendAppointmentStatusNotification($inputs = [], $appointment = [], $notificationType = 'change_appointment_status') {
        if ($inputs['login_user_type'] == 'freelancer') {

            $receiver_data = self::processCustomerReceiver($inputs);

            $sender_data = self::processFreelancerSender($inputs);
        } else {
            $receiver_data = self::processFreelancerReceiver($inputs);
            $sender_data = self::processCustomerSender($inputs);
        }
        $data = [];
        $data['sender'] = $sender_data['sender'];
        $data['receiver'] = $receiver_data['receiver'];
        $data['appointment']['appointment_uuid'] = $appointment['appointment_uuid'];
        $message = ($inputs['login_user_type'] == "freelancer") ? ('has ' . $inputs['status'] . ' your booking') : ('has ' . $inputs['status'] . ' their booking');
        $sender_name = $sender_data['sender']['first_name'] ?? '';
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_name . $message,
            'save_message' => $message,
            'data' => $data,
            'appointment_uuid' => $appointment['appointment_uuid'],
        ];

        $notification_inputs = self::prepareAppointmentNotificationInputs($messageData);

        $save_notification = Notification::addNotification($notification_inputs);

        if (!$save_notification) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(AppointmentValidationHelper::changeAppointmentStatusRules()['message_' . strtolower($inputs['lang'])]['update_appointment_error']);
        }

        if (!empty($data['receiver'])) {
            $messageData['receiver_id'] = !empty($data['receiver']['user_id']) ? $data['receiver']['user_id'] : null;
            return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['user_id'] : $data['receiver']['user_id'], $messageData);
//        $check_notification_setting = NotificationSetting::getSettingsWithType('profile_uuid', $receiver_data['receiver']['freelancer_uuid'], $inputs['status']);
//        if (!empty($receiver_data['device_token'])) {
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
        }
    }

    public static function sendMessageNotification($data = [], $receiver = [], $loginUser, $isCallback, $notificationType = 'new_message') {
        $uuid = "";
        if ($data['reciever_type'] == "customer") {
            $receiver_data = self::processChatCustomer($receiver);
            $uuid = $receiver_data['uuid'];
        } elseif ($data['reciever_type'] == "freelancer") {
            $receiver_data = self::processChatFreelancer($receiver);
            $uuid = $receiver_data['uuid'];
        }
        if ($data['sender']['type'] == "customer") {
            $sender_data = self::processChatCustomer($loginUser);
        } elseif ($data['sender']['type'] == "freelancer") {
            $sender_data = self::processChatFreelancer($loginUser);
        }
//        $receiver_token = UserDevice::getUserDevice('profile_uuid', $uuid);
        $message_data = self::prepareMessageData($data);
        $message_data['sender'] = $sender_data;
        $message_data['receiver'] = $receiver_data;
        $messageData = [
            'type' => $notificationType,
            'alert-message' => $sender_data['name'] . ' has sent you a message',
            'save_message' => '  has sent you a new message',
            'message' => !empty($message_data['message']) ? ($message_data['message']) : "",
            'chat_with' => !empty($message_data['chat_with']) ? ($message_data['chat_with']) : "user",
            'message_uuid' => $message_data['message_uuid'],
            'id' => $message_data['id'],
            'media' => !empty($message_data['media']) ? $message_data['media'] : [],
            'sender' => $message_data['sender'],
//            'receiver' => $message_data['receiver'],
//            'receiver_name' => $message_data['receiver']['name'],
            'receiver_uuid' => $message_data['receiver']['id'],
            'receiver_id' => $message_data['receiver']['user_id'],
            'receiver_type' => $message_data['receiver']['type'],
            'created_on' => $message_data['created_on'],
//            'data' => $message_data,
            'notification_send_type' => 'mutable',
        ];
        $uuid = !empty($message_data['receiver']['user_id']) ? $message_data['receiver']['user_id'] : null;
        return PushNotificationHelper::send_notification_to_user_devices($uuid, $messageData, "chat");
//        if (!empty($receiver_token['device_token'])) {
////        return PushNotificationHelper::send_voip_notification_to_user($receiver_data['voip_device_token'], $messageData);
//            return PushNotificationHelper::send_notification_to_user($receiver_token['device_token'], $messageData);
//
////            return PushNotificationHelper::send_notification_to_user("1043cb44ca36801c17aec373ff28bd0c7bfcb0ad52ccf3ce0e94928806d1d09b", $messageData);
//        }
    }

    public static function prepareMessageData($message_data = []) {
        $data = [];
        if (!empty($message_data)) {
            $data['message'] = !empty($message_data['message']) ? $message_data['message'] : "";
            $data['message_uuid'] = $message_data['message_uuid'];
            $data['id'] = $message_data['id'];
            $data['created_on'] = $message_data['created_on'];
            if (isset($message_data['media']) && !empty($message_data['media'])) {
                $data['media'] = self::prepareMessageMedia($message_data['media']);
            }
        }
        return $data;
    }

    public static function prepareMessageMedia($media = []) {
        if (!empty($media)) {
            $data['width'] = $data['height'] = null;
            $data = [
                'attachment_type' => !empty($media['attachment_type']) ? $media['attachment_type'] : "",
                'attachment' => !empty($media['attachment']) ? $media['attachment'] : null,
                'video_thumbnail' => null,
            ];
            if ($media['attachment_type'] == "video") {
                $thumb = explode(".", $media['video_thumbnail']);
                $data = [
                    'attachment_type' => !empty($media['attachment_type']) ? $media['attachment_type'] : "",
                    'attachment' => !empty($media['attachment']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['message_attachments'] . $media['attachment'] : null,
                    'video_thumbnail' => !empty($media['attachment']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['post_video_thumb'] . $thumb[0] . ".jpg" : null,
                ];
                $resolution = !empty($data['video_thumbnail']) ? getimagesize($data['video_thumbnail']) : [];
                $data['media']['width'] = ((isset($resolution[0])) && (!empty($resolution[0]))) ? (float) $resolution[0] : null;
                $data['media']['height'] = ((isset($resolution[1])) && (!empty($resolution[1]))) ? (float) $resolution[1] : null;
            }

            if ($media['attachment_type'] == "image") {
                $resolution = !empty($media['attachment']) ? getimagesize($data['attachment']) : [];
                $data['media']['width'] = ((isset($resolution[0])) && (!empty($resolution[0]))) ? (float) $resolution[0] : null;
                $data['media']['height'] = ((isset($resolution[1])) && (!empty($resolution[1]))) ? (float) $resolution[1] : null;
            }
        }
        return $data;
    }

    public static function sendMultipleClassBookingNotification($data = [], $notificationType = 'new_class_booking') {
        if ($data['login_user_type'] == "customer") {
            $data['customer_id'] = CommonHelper::getCutomerIdByUuid($data['logged_in_uuid']);
            $sender_data = self::processCustomerSender($data);
            $receiver_data = self::processFreelancerReceiver($data);

            return self::processClassPackgeNotificationData($sender_data, $receiver_data, $data, $notificationType = 'class_package');
        }
        return true;
    }

    public static function processClassPackgeNotificationData($sender_data = [], $receiver_data = [], $notification_data = [], $notificationType = 'class_package') {
        $data = [];
        $data['sender'] = [];
        $data['receiver'] = [];
        if (!empty($sender_data['sender'])) {
            $data['sender'] = $sender_data['sender'];
        }
        if (!empty($receiver_data['receiver'])) {
            $data['receiver'] = $receiver_data['receiver'];
        }
        $data['class']['class_uuid'] = $notification_data['booking'][0]['class_uuid'];
        $data['class']['class_schedule_uuid'] = $notification_data['booking'][0]['class_schedule_uuid'];
        $data['class']['package_uuid'] = $notification_data['booking'][0]['package_uuid'];
        $data['class']['purchase_time'] = $notification_data['purchase_time'];
        $messageData = [
            'type' => $notificationType,
            'message' => $sender_data['sender']['first_name'] . ' has booked a class pass',
            'save_message' => ' has booked a class pass',
            'data' => $data,
            'class_uuid' => $notification_data['booking'][0]['class_uuid'],
            'class_schedule_uuid' => $notification_data['booking'][0]['class_schedule_uuid'],
            'package_uuid' => $notification_data['booking'][0]['package_uuid'],
            'purchase_time' => $notification_data['purchase_time'],
        ];
        $notification_inputs = self::prepareClassPackgeNotificationInputs($messageData);

        $save_notification = Notification::addNotification($notification_inputs);

        return PushNotificationHelper::send_notification_to_user_devices(!empty($data['receiver']['freelancer_uuid']) ? $data['receiver']['freelancer_uuid'] : $data['receiver']['customer_uuid'], $messageData);
//        if (!empty($receiver_data['device_token'])) {
//            return PushNotificationHelper::send_notification_to_user($receiver_data['device_token'], $messageData);
//        }
    }

    public static function prepareClassPackgeNotificationInputs($messageData = []) {
        $notification_inputs = [];

        if (isset($messageData['data']['receiver']['freelancer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['receiver']['customer_uuid'])) {
            $notification_inputs['receiver_id'] = $messageData['data']['receiver']['user_id'];
        }
        if (isset($messageData['data']['sender']['customer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
        }
        if (isset($messageData['data']['sender']['freelancer_uuid'])) {
            $notification_inputs['sender_id'] = $messageData['data']['sender']['user_id'];
        }
        $notification_inputs['uuid'] = $messageData['class_uuid'];
        $notification_inputs['class_schedule_uuid'] = !empty($messageData['class_schedule_uuid']) ? $messageData['class_schedule_uuid'] : null;
        $notification_inputs['package_uuid'] = !empty($messageData['package_uuid']) ? $messageData['package_uuid'] : null;
        $notification_inputs['purchase_time'] = !empty($messageData['purchase_time']) ? $messageData['purchase_time'] : null;
        $notification_inputs['date'] = !empty($messageData['class_date']) ? $messageData['class_date'] : null;
        $notification_inputs['message'] = $messageData['save_message'];
        $notification_inputs['notification_type'] = $messageData['type'];
        $notification_inputs['is_read'] = 0;

        return $notification_inputs;
    }

    public static function sendFreelancerNotification($appointment_data, $freelancer = [], $customer = [], $notificationType = 'appointment_reminder') {
        if (!empty($appointment_data)) {
            $notificationType = "appointment_reminder";
            $data = [];
            $sender_data = \App\Helpers\ProcessNotificationHelper::processCustomer($customer);
            $receiver_data = \App\Helpers\ProcessNotificationHelper::processFreelancer($freelancer);
            $device = UserDevice::getUserDevice('profile_uuid', $freelancer['freelancer_uuid']);
            $data['sender'] = [];
            $data['receiver'] = [];
            $data['appointment']['appointment_uuid'] = $appointment_data['appointment_uuid'];
            if (!empty($sender_data)) {
                $data['sender'] = $sender_data;
            }
            if (!empty($receiver_data)) {
                $data['receiver'] = $receiver_data;
            }
            $messageData = [
                'type' => $notificationType,
                'message' => 'You have a pending booking request with ' . $sender_data['first_name'],
                'data' => $data,
                'appointment_uuid' => $appointment_data['appointment_uuid'],
            ];
            $check_notification_setting = NotificationSetting::getSettingsWithType('profile_uuid', $freelancer['freelancer_uuid'], 'new_appointment');
//            if (!empty($device['device_token']) && !empty($check_notification_setting)) {
            if (!empty($check_notification_setting)) {
//                return PushNotificationHelper::send_notification_to_user($device['device_token'], $messageData);
                return PushNotificationHelper::send_notification_to_user_devices($freelancer['freelancer_uuid'], $messageData);
            }
        }
    }

}

// end of helper class
