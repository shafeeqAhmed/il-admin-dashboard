<?php

namespace App\Helpers;

use App\Favourite;
use App\Freelancer;
use App\Subscription;
use App\Profession;
use App\Appointment;
use App\Message;
use App\Classes;
use App\ClassBooking;
use App\Customer;
use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Validator;

Class FreelancerSearchHelper {
    /*
      |--------------------------------------------------------------------------
      | FreelancerSearchHelper that contains all the freelancer related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use freelancer processes
      |
     */

    /**
     * Description of FreelancerSearchHelper
     *
     * @author ILSA Interactive
     */
    public static function searchFreelancers($inputs) {
        $inputs['subscription_ids'] = [];
        $inputs['logged_in_id'] = CommonHelper::getRecordByUserType($inputs['login_user_type'], $inputs['logged_in_uuid']);

        if (!empty($inputs['is_subscribed']) && $inputs['is_subscribed'] == true) {
            $subscription_ids = Subscription::getSubscribedIds('subscriber_uuid', $inputs['logged_in_uuid']);
            $inputs['subscription_ids'] = $subscription_ids;
        }

        $limit = !empty($inputs['limit']) ? $inputs['limit'] : null;
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : null;
        $result = Freelancer::searchFreelancers($inputs, $limit, $offset);
        $response = self::makeFreelancersListResponse($result);
        return CommonHelper::jsonSuccessResponse(FollowerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function makeFreelancersListResponse($data_array = []) {
        $response = [];
        $url = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'production';
        if (!empty($data_array)) {
            foreach ($data_array as $key => $data) {

//                dd($data['freelancer_categories']);
//                $freelancer_categories = CategoryHelper::prepareSearchFreelancerCategoryResponse($data['freelancer_categories']);
//                $flag_online = self::checkOnlineFaceToFaceFlag($freelancer_categories);
                $response[$key]['freelancer_uuid'] = $data['freelancer_uuid'];
                $response[$key]['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
                $response[$key]['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
                $response[$key]['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
//                $response[$key]['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
                $response[$key]['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['profile_image']);
                $response[$key]['profile_card_image'] = !empty($data['profile_card_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_card_image'] : null;
                $response[$key]['lat'] = !empty($data['primary_location']['location']['lat']) ? $data['primary_location']['location']['lat'] : null;
                $response[$key]['lng'] = !empty($data['primary_location']['location']['lng']) ? $data['primary_location']['location']['lng'] : null;
//                $response[$key]['provides_online_services'] = $flag_online['is_online'];
//                $response[$key]['provides_face_to_face'] = $flag_online['is_face_to_face'];
                $response[$key]['is_liked'] = (!empty($data['likes_count']) && $data['likes_count'] > 0) ? true : false;
                $response[$key]['is_following'] = (!empty($data['following']) && $data['following'] > 0) ? true : false;
                $response[$key]['is_favourite'] = (!empty($data['favourites']) && $data['favourites'] > 0) ? true : false;
                $response[$key]['is_subscription_profiles'] = (!empty($data['subscriptions']) && $data['subscriptions'] > 0) ? true : false;
                $response[$key]['country'] = self::extractCountryCityName($data, 'country');
                $response[$key]['city'] = self::extractCountryCityName($data, 'city');
                $response[$key]['share_url'] = 'www.google.com';
                $response[$key]['reviews_count'] = $data['reviews_count'];
                $response[$key]['average_rating'] = (isset($data['reviews']) && !empty($data['reviews'][0])) ? (float) $data['reviews'][0]['average_rating'] : 0;
//                $response[$key]['freelancer_categories'] = $freelancer_categories;
                $data_string = "freelancer_uuid=" . $data['freelancer_uuid'] . "&currency=" . $data['default_currency'];

                $encoded_string = base64_encode($data_string);
                if (strpos($url, 'localhost') !== false) {
                    $response[$key]['share_url'] = "localhost/boatekapi/getFreelancerProfile" . "?" . $encoded_string;
                } elseif (strpos($url, 'staging') !== false) {
                    $response[$key]['share_url'] = config("general.url.staging_url") . "getFreelancerProfile?" . $encoded_string;
//                $response['share_url'] = config("general.url.staging_url") . "getFreelancerProfile?freelancer_uuid=" . $data['freelancer_uuid'] . '&currency=' . $data['default_currency'];
                } elseif (strpos($url, 'dev') !== false) {
                    $response[$key]['share_url'] = config("general.url.development_url") . "getFreelancerProfile?" . $encoded_string;
                } elseif (strpos($url, 'production') !== false) {
                    $response[$key]['share_url'] = config("general.url.production_url") . "getFreelancerProfile?" . $encoded_string;
                }
            }
        }

        return $response;
    }

    public static function extractCountryCityName($data, $column) {
        $value = '';
        if (isset($data['locations']) && !empty($data['locations'])) {
            foreach ($data['locations'] as $location) {
                if (!empty($location['location'])) {
                    $value = $location['location'][$column];
                }
            }
        }
        return $value;
    }

    public static function checkOnlineFaceToFaceFlag($freelancer_categories) {
        $response = [];
        $is_online = false;
        $is_face_to_face = false;
        if (!empty($freelancer_categories)) {
            foreach ($freelancer_categories as $cateory) {
                if ($cateory['is_online'] == 1) {
                    $is_online = true;
                    break;
                }
            }
            foreach ($freelancer_categories as $cateory) {
                if ($cateory['is_online'] == 0) {
                    $is_face_to_face = true;
                    break;
                }
            }
        }
        $response['is_online'] = $is_online;
        $response['is_face_to_face'] = $is_face_to_face;
        return $response;
    }

    public static function searchChatUsers($inputs) {
        $validation = Validator::make($inputs, ChatValidationHelper::searchChatUsersRules()['rules'], ChatValidationHelper::searchChatUsersRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $response = [];
        $search_key = !empty($inputs['search_key']) ? $inputs['search_key'] : null;
        $limit = !empty($inputs['limit']) ? $inputs['limit'] : null;
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : null;
        $get_subscription_ids = [];
        $get_appointment_ids = [];
        $prepare_inbox_ids = [];
        $get_booking_ids = [];
        $get_user_ids = [];
        if ($inputs['login_user_type'] == "freelancer") {
            $freelancer = CommonHelper::getFreelancerIdByUuid($inputs['logged_in_uuid']);
            $inputs['login_user_id'] = $freelancer;
//            $get_subscription_ids = Subscription::getFavouriteProfileIds('subscribed_id', $inputs['login_user_id'], 'subscriber_id');
            $get_appointment_ids = Appointment::getFavIdsOfFutureAppointments('freelancer_id', $freelancer, 'customer_id');
//            $get_class_ids = Classes::getClassIds('freelancer_id', $inputs['login_user_id']);
//            $get_booking_ids = ClassBooking::getFavIds('class_id', $get_class_ids, 'customer_id');
            $get_inbox_ids = Message::pluckFavIds($freelancer);
            $prepare_inbox_ids = !empty($get_inbox_ids) ? self::prepareCustomerInboxIds($get_inbox_ids) : [];
            $merge_array = array_values(array_unique(array_merge($get_appointment_ids, $prepare_inbox_ids), SORT_REGULAR));
            $get_user_ids = Customer::getIds('user_id', $merge_array);
        } elseif ($inputs['login_user_type'] == "customer") {
            $customer = Customer::getSingleCustomerDetail('customer_uuid', $inputs['logged_in_uuid']);
            $inputs['login_user_id'] = $customer['id'];
//            $get_subscription_ids = Subscription::getFavouriteProfileIds('subscriber_id', $inputs['login_user_id'], 'subscribed_id');
            $get_appointment_ids = Appointment::getFavIdsOfFutureAppointments('customer_id', $customer, 'freelancer_id');
//            $get_class_ids = ClassBooking::pluckClassBookingIds('customer_id', $inputs['login_user_id'], 'class_id');
//            $get_booking_ids = Classes::pluckFavoriteIds('class_id', $get_class_ids, 'freelancer_id');
            $get_inbox_ids = Message::pluckFavIds($customer['user_id']);
            $prepare_inbox_ids = !empty($get_inbox_ids) ? self::prepareFreelancerInboxIds($get_inbox_ids) : [];
            $get_user_ids = array_values(array_unique(array_merge($get_appointment_ids, $prepare_inbox_ids), SORT_REGULAR));
        } else {
            return CommonHelper::jsonErrorResponse("Invalid type provided");
        }
//        $results = Customer::searchCustomersForChat($search_key, $get_subscriber_ids, $limit, $offset);

        $results = ($inputs['login_user_type'] == "customer") ? Freelancer::searchFreelancersForChat($search_key, $get_user_ids, $limit, $offset) : \App\User::searchCustomersForChat($search_key, $get_user_ids, $limit, $offset);
        $response = self::redirectResponseCall($inputs, ($results) ? $results : []);
        return CommonHelper::jsonSuccessResponse(FollowerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function prepareCustomerInboxIds($data = []) {
        $ids = [];
        foreach ($data as $key => $record) {
            if ($record['receiver_type'] == "customer" || $record['sender_type'] == "customer") {
                if (($record['receiver_type'] == "customer") && !empty($record['receiver_id']) && !in_array($record['receiver_id'], $ids)) {
                    array_push($ids, $record['receiver_id']);
                } elseif (($record['sender_type'] == "customer") && !empty($record['sender_id']) && !in_array($record['sender_id'], $ids)) {
                    array_push($ids, $record['sender_id']);
                }
            }
        }
        return ($ids) ? $ids : [];
    }

    public static function prepareFreelancerInboxIds($data = []) {
        $ids = [];
        foreach ($data as $key => $record) {
            if ($record['receiver_type'] == "freelancer" || $record['sender_type'] == "freelancer") {
                if (($record['receiver_type'] == "freelancer") && !empty($record['receiver_id']) && !in_array($record['receiver_id'], $ids)) {
                    array_push($ids, $record['receiver_id']);
                } elseif (($record['sender_type'] == "freelancer") && !empty($record['sender_id']) && !in_array($record['sender_id'], $ids)) {
                    array_push($ids, $record['sender_id']);
                }
            }
        }
        return ($ids) ? $ids : [];
    }

    public static function redirectResponseCall($inputs = [], $results = []) {
        $response = [];
        $data = [];
        if ($inputs['login_user_type'] == "freelancer") {
            $data = InboxHelper::getChatRelatedFreelancerData($inputs);
            $response = SearchResponseHelper::searchedCustomersResponse($results, $data);
        }
        if ($inputs['login_user_type'] == "customer") {
            $data = InboxHelper::getChatRelatedCustomerData($inputs);
            $response = SearchResponseHelper::searchedFreelancersResponse($results, $data);
        }
        return !empty($response) ? $response : [];
    }

    public static function getFreelancersFeed($inputs) {

        $limit = !empty($inputs['limit']) ? $inputs['limit'] : null;
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : null;
        $freelancers = Freelancer::searchFreelancers($inputs, $limit, $offset);
        $records = [];
        if (!empty($freelancers)) {
            foreach ($freelancers as $freelancer) {
                $data['is_favourite'] = Favourite::checkFavourite($freelancer['id'], $inputs['login_user_id']);
//                $singleRecord = Freelancer::getFreelancerDetail('freelancer_uuid', $freelancer['freelancer_uuid']);
//                $boat = FreelancerResponseHelper::freelancerProfileResponse($singleRecord,$data);
                $boat = FreelancerResponseHelper::freelancerProfileResponse($freelancer, $data, 'UTC', $inputs);
                if (!empty($boat)) {
                    $records[] = $boat;
                }
            }
        }
        return CommonHelper::jsonSuccessResponse(FollowerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $records);
    }

}

?>
