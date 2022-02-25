<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;
use App\Message;
use App\Subscription;
use App\Follower;
use App\ClassBooking;
use App\Classes;
use App\Appointment;
use App\Customer;
use App\Freelancer;
use App\User;
use DB;

Class InboxHelper {
    /*
      |--------------------------------------------------------------------------
      | InboxHelper that contains chat related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use chat processes
      |
     */

    /**
     * Description of InboxHelper
     *
     * @author ILSA Interactive
     */
    public static function getInboxMessage($inputs = []) {
        $validation = Validator::make($inputs, ChatValidationHelper::getInboxMessagesRules()['rules'], ChatValidationHelper::getInboxMessagesRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $validate_inputs = $inputs;
        $validate_inputs['type'] = $inputs['login_user_type'];
        $validate_loggedin_user = ChatHelper::validateLoggedInUser($validate_inputs);
        if (!$validate_loggedin_user['success']) {
            return CommonHelper::jsonErrorResponse($validate_loggedin_user['message']);
        }
//        if ($inputs['login_user_type'] == "freelancer") {
//            $data = self::getChatRelatedFreelancerData($inputs);
//        } elseif ($inputs['login_user_type'] == "customer") {
//            $data = self::getChatRelatedCustomerData($inputs);
//        }
        $inputs['login_user_id'] = $validate_loggedin_user['data']['id'];
        $inputs['key'] = CommonHelper::prepareCompositeKey($inputs['login_user_id'], $inputs['login_user_type']);
        $messages = Message::getInboxMessages($inputs, $inputs['not_in'], $inputs['limit'], $inputs['offset']);
        \Log::info("flow is here");
        \Log::info(print_r($messages, true));
        $response = ChatResponseHelper::inboxMessagesResponse($inputs, $messages);
//        $response = ChatResponseHelper::inboxMessagesResponse($inputs, $messages, $data);
        return CommonHelper::jsonSuccessResponse("Successful Request", $response);
    }

    public static function prepareCirclAdminResponse($inputs) {
        $response = [];
        $get_admin = Customer::getAdminDetail('type', 'admin');
        if (!empty($get_admin)) {
            $response['name'] = "Team Circl";
            $response['type'] = "customer";
            $response['chat_with'] = "admin";
            $response['uuid'] = $get_admin['customer_uuid'];
            $response['label'] = "Welcome to circl..";
        }
    }

    public static function getChatRelatedFreelancerData($inputs = []) {
        $data = [];
        $data['subscribers'] = $data['followers'] = $data['appointment_customer_ids'] = [];
        $data['customer_class_ids'] = [];
        $data['class_ids'] = [];
        if (!empty($inputs)) {
//            $data['subscribers'] = Subscription::getFavouriteProfileIds('subscribed_id', $inputs['login_user_id'], 'subscriber_id');
//            $data['followers'] = Follower::getParticularIds('following_id', $inputs['login_user_id'], 'follower_id');
//            $data['class_ids'] = Classes::getClassUuids('freelancer_id', $inputs['login_user_id']);
//            if (!empty($data['class_ids'])) {
//                $data['customer_class_ids'] = ClassBooking::pluckClassBookingIds('class_id', $data['class_ids'], 'customer_id');
//            }
            $data['appointment_customer_ids'] = Appointment::pluckFavIds('freelancer_id', $inputs['login_user_id'], 'customer_id');
        }
        return $data;
    }

    public static function getChatRelatedCustomerData($inputs = []) {
        $data = [];
        $data['class_freelancer_ids'] = [];
        $data['subscribed_ids'] = $data['followings'] = $data['booking_ids'] = $data['appointment_freelancer_ids'] = [];
        if (!empty($inputs)) {
//            $data['subscribed_ids'] = Subscription::getFavouriteProfileIds('subscriber_id', $inputs['login_user_id'], 'subscribed_id');
//            $data['followings'] = Follower::getParticularIds('follower_id', $inputs['login_user_id'], 'following_id');

//            $data['booking_ids'] = ClassBooking::pluckClassBookingIds('customer_id', $inputs['login_user_id'], 'class_id');
//            if (!empty($data['booking_ids'])) {
//                $data['class_freelancer_ids'] = Classes::pluckFavoriteIds('class_id', $data['booking_ids'], 'freelancer_id');
//            }
            $data['appointment_freelancer_ids'] = Appointment::pluckFavIds('customer_id', $inputs['login_user_id'], 'freelancer_id');
        }
        return $data;
    }

    public static function getChatConversation($inputs = []) {
        $validation = Validator::make($inputs, ChatValidationHelper::getChatConversationRules()['rules'], ChatValidationHelper::getChatConversationRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $validate_inputs = $inputs;
        $validate_inputs['profile_uuid'] = $inputs['logged_in_uuid'];
        $validate_inputs['type'] = $inputs['login_user_type'];
        $validate_loggedin_user = ChatHelper::validateLoggedInUser($validate_inputs);
        if (!$validate_loggedin_user['success']) {
            return CommonHelper::jsonErrorResponse($validate_loggedin_user['message']);
        }
//        \Log::info("-- Inbox final inputs ---");
//        \Log::info(print_r($inputs, true));



        if ($loginUser = $this->validateLoginUser($inputs)) {
            $inputs = $this->prepareConversationInputs($inputs);
            if (!$this->validateChatInput($inputs)) {
//                    return response()->json(['message' => $this->errors['otherUserInfoMissing']], 400);
                return CommonHelper::jsonErrorResponse($this->errors['otherUserInfoMissing']);
            }
            return $this->getConversation($inputs, $loginUser);
        }
//            return response()->json((['message' => $this->errors['noUserFound']]), 400);
        return CommonHelper::jsonErrorResponse($this->errors['noUserFound']);

        return CommonHelper::jsonSuccessResponse("Successful Request", $response);
    }

    public static function getUnreadChatCount($profile_uuid, $type) {
        if (!empty($profile_uuid)) {
            $key = CommonHelper::prepareCompositeKey($profile_uuid, $type);
            $get_count = Message::unreadChatCount('receiver_key', $key);
//            $count = !empty($get_count) ? count($get_count) : null;
            $count = !empty($get_count) ? ($get_count) : 0;
        }
        return isset($count) ? $count : 0;
    }

    public static function UpdateAllChatStatus($profile_uuid) {
        $status = ['status' => 'viewed'];
        $update = Message::updateAllStatus($status);
        return true;
    }

    public static function updateChatStatus($inputs) {
        $validation = Validator::make($inputs, ChatValidationHelper::UpdateChatStatusRules()['rules'], ChatValidationHelper::UpdateChatStatusRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $ids['freelancer_id'] = null;
        $ids['customer_id'] = null;
        $status = ['status' => 'viewed'];
        if ($inputs['other_user_type'] == "customer") {
            $other_user = User::checkUser('user_uuid', $inputs['other_user_uuid']);
            $ids['customer_id'] = ((isset($other_user['user_customer']['id'])) && (!empty($other_user['user_customer']['id']))) ? $other_user['user_customer']['id'] : null;
        }
        if ($inputs['login_user_type'] == "customer") {
            $login_user = User::checkUser('user_uuid', $inputs['logged_in_uuid']);
            $ids['customer_id'] = ((isset($login_user['user_customer']['id'])) && (!empty($login_user['user_customer']['id']))) ? $login_user['user_customer']['id'] : null;
        }
        if ($inputs['other_user_type'] == "freelancer") {
            $other_user = Freelancer::checkFreelancer('freelancer_uuid', $inputs['other_user_uuid']);
            $ids['freelancer_id'] = ((isset($other_user['id'])) && (!empty($other_user['id']))) ? $other_user['id'] : null;
        }
        if ($inputs['login_user_type'] == "freelancer") {
            $login_user = Freelancer::checkFreelancer('freelancer_uuid', $inputs['logged_in_uuid']);
            $ids['freelancer_id'] = ((isset($login_user['id'])) && (!empty($login_user['id']))) ? $login_user['id'] : null;
        }
        if ((empty($login_user)) || (empty($other_user))) {
            return CommonHelper::jsonErrorResponse(ChatValidationHelper::UpdateChatStatusRules()['message_' . strtolower($inputs['lang'])]['update_status_error']);
        }
        $other_user_key = CommonHelper::prepareCompositeKey($other_user['id'], $inputs['other_user_type']);
        $login_user_key = CommonHelper::prepareCompositeKey($login_user['id'], $inputs['login_user_type']);
        $check_chat = Message::checkChat($other_user_key, $login_user_key);
//        $check_chat = Message::checkChat($other_user['id'], $login_user['id']);
        if (!empty($check_chat)) {
            $last_sender = $check_chat[0]['sender_id'];
        }
        if (!empty($last_sender)) {
            if ($login_user['id'] != $last_sender) {
                $update_status = Message::updateMessagesStatus($login_user_key, $other_user_key, $status);
//                if (!$update_status) {
//                    DB::rollback();
//                    return CommonHelper::jsonErrorResponse(ChatValidationHelper::UpdateChatStatusRules()['message_' . strtolower($inputs['lang'])]['update_status_error']);
//                }
            }
        }
//        $get_user = self::getUserPublicChat($inputs);
        $chat_data = ClientHelper::getClientChatData($ids);
        $chat_data['public_chat'] = ($other_user['public_chat'] == 1) ? true : false;
        $response = self::prepareChatDataResponse($chat_data);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function getUserPublicChat($inputs = []) {
        $data = [];
        if (!empty($inputs)) {
            if ($inputs['other_user_type'] == "freelancer") {
                $data = Freelancer::checkFreelancer('freelancer_uuid', $inputs['other_user_uuid']);
            } elseif ($inputs['other_user_type'] == "customer") {
                $data = Customer::getSingleCustomerDetail('customer_uuid', $inputs['other_user_uuid']);
            }
        }
        return $data;
    }

    public static function prepareChatDataResponse($chat_data = []) {
        $response = [];
        if (!empty($chat_data)) {
//            $response['has_subscribed'] = ($chat_data['has_subscription'] == 1) ? true : false;
            $response['has_appointment'] = ($chat_data['has_appointment'] == 1) ? true : false;
//            $response['has_followed'] = ($chat_data['has_followed'] == 1) ? true : false;
            $response['public_chat'] = ($chat_data['public_chat'] == 1) ? true : false;
        }
        return $response;
    }

}

?>
