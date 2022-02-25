<?php

namespace App\Helpers;

use Twilio\Rest\Preview\TrustedComms\CpsContext;

Class FreelancerResponseHelper {
    /*
      |--------------------------------------------------------------------------
      | FreelancerResponseHelper that contains all the Freelancer response methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use Freelancer processes
      |
     */

    public static function prepareSignupResponse($data = []) {
        $response = [];
        $response['freelancer_uuid'] = $data['freelancer_uuid'];
        $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
        $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
        $response['company'] = !empty($data['company']) ? $data['company'] : null;
        $response['email'] = !empty($data['email']) ? $data['email'] : null;
        $response['profile_type'] = !empty($data['profile_type']) ? $data['profile_type'] : 0;
        $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
        $response['country_code'] = !empty($data['country_code']) ? $data['country_code'] : null;
        $response['country_name'] = !empty($data['country_name']) ? $data['country_name'] : null;
        $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
//      $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
        $response['profile_images'] = self::freelancerProfileImagesResponse($data['profile_image']);
        $response['cover_image'] = !empty($data['cover_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $data['cover_image'] : null;
        $response['cover_images'] = self::freelancerCoverImagesResponse($data['cover_image']);
        $response['gender'] = !empty($data['gender']) ? $data['gender'] : null;
        $response['onboard_count'] = !empty($data['onboard_count']) ? $data['onboard_count'] : null;
        $response['locations'] = LoginHelper::processFreelancerLocationsResponse((!empty($data['locations']) ? $data['locations'] : []));
        $response['can_travel'] = !empty($data['can_travel']) ? $data['can_travel'] : 0;
        $response['travelling_distance'] = !empty($data['travelling_distance']) ? $data['travelling_distance'] : null;
        $response['travelling_cost_per_km'] = !empty($data['travelling_cost_per_km']) ? $data['travelling_cost_per_km'] : null;
        $response['qualifications'] = self::freelancerQualificationResponse(!empty($data['qualifications']) ? $data['qualifications'] : []);
        $response['currency'] = !empty($data['default_currency']) ? $data['default_currency'] : null;
        //$response['receive_subscription_request'] = $data['receive_subscription_request'];
        $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
        $response['has_bank_detail'] = ($data['has_bank_detail'] == 1) ? true : false;
        return $response;
    }

    public static function freelancerProfileImagesResponse($image_key = null) {
        $response = null;
        $response['1122'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_1122'] . $image_key : null;
        $response['420'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_420'] . $image_key : null;
        $response['336'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_336'] . $image_key : null;
        $response['240'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_240'] . $image_key : null;
        $response['96'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_96'] . $image_key : null;
        $response['orignal'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $image_key : null;
        return $response;
    }

    public static function freelancerCoverImagesResponse($image_key = null) {
        $response = null;
        $response['1122'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_1122'] . $image_key : null;
        $response['420'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_420'] . $image_key : null;
        $response['336'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_336'] . $image_key : null;
        $response['240'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_240'] . $image_key : null;
        $response['96'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_thumb_96'] . $image_key : null;
        $response['orignal'] = !empty($image_key) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $image_key : null;
        return $response;
    }

    public static function freelancerProfileResponse($data = [], $extra_features = [], $local_timezone = 'UTC', $inputs = [], $to_currency = 'SAR') {
        $response = [];
        if (!empty($data)) {
            //  $freelancer_categories = CategoryHelper::prepareSearchFreelancerCategoryResponse($data['freelancer_categories']);
            // $flag_online = FreelancerSearchHelper::checkOnlineFaceToFaceFlag($freelancer_categories);
            $freelancer_id = CommonHelper::getRecordByUuid('freelancers', 'freelancer_uuid', $data['freelancer_uuid']);
            $response['freelancer_uuid'] = $data['freelancer_uuid'];
            $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
            $response['onboard_count'] = !empty($data['onboard_count']) ? $data['onboard_count'] : null;
            $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
            $response['boat_name'] = !empty($data['first_name']) ? $data['first_name'] . ' ' . $data['last_name'] : null;
            $response['username'] = !empty($data['user']) ? $data['user']['first_name'] . ' ' . $data['user']['last_name'] : null;

            $response['company'] = !empty($data['company']) ? $data['company'] : null;
            $response['company_logo'] = !empty($data['company_logo']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['company_logo'] . $data['company_logo'] : null;
            $response['email'] = !empty($data['email']) ? $data['email'] : null;
            $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
            $response['country_code'] = !empty($data['country_code']) ? $data['country_code'] : null;
            $response['country_name'] = !empty($data['country_name']) ? $data['country_name'] : null;
            $response['manufacturer'] = !empty($data['manufacturer']) ? $data['manufacturer'] : null;
//          $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
            $response['profile_images'] = self::freelancerProfileImagesResponse($data['profile_image']);
            $response['profile_card_image'] = !empty($data['profile_card_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_card_image'] : null;
            $response['cover_image'] = self::freelancerCoverSingleImageResponse($data);
            $response['cover_images'] = self::freelancerCoverImagesResponse($data['cover_image']);
            $response['cover_video'] = !empty($data['cover_video']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['cover_video'] . $data['cover_video'] : null;
            $response['cover_video_thumb'] = !empty($data['cover_video_thumb']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['cover_video'] . $data['cover_video_thumb'] : null;
            $response['public_chat'] = !empty($data['public_chat'] == 1) ? true : false;
//          $response['cover_video_thumb'] = "http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5e985179c40311587040633.jpg";

            $response['gender'] = !empty($data['user']['gender']) ? $data['user']['gender'] : null;
            $response['booking_preferences'] = !empty($data['booking_preferences']) ? $data['booking_preferences'] : null;
            $response['business_cover_image'] = (!empty($data['freelancer_categories']) && !empty($data['freelancer_categories'][0]['category']) && !empty($data['freelancer_categories'][0]['category']['image'])) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['category_image'] . $data['freelancer_categories'][0]['category']['image'] : null;
            $response['bio'] = !empty($data['bio']) ? $data['bio'] : null;
            $response['followers_count'] = !empty($extra_features['followers_count']) ? $extra_features['followers_count'] : 0;
            $response['reviews_count'] = !empty($data['reviews']) ? count($data['reviews']) : 0;
            $review_avg = array_sum(array_column($data['reviews'], 'rating'));
            $response['reviews_avg'] = $response['reviews_count'] > 0 && $review_avg > 0 ? round($review_avg / $response['reviews_count'], 2) : 0;
            $response['per_hour_price'] = BoatHelper::BoatPriceObject($data);
            $response['post_count'] = !empty($extra_features['post_count']) ? $extra_features['post_count'] : 0;
            $response['price'] = $data['price'];
//            $response['has_story'] = true;
            $response['has_story'] = !empty($extra_features['story']) ? true : false;
            //validated data for check is_seen in story
            $response['stories'] = !empty($extra_features['story']) ? StoryResponseHelper::processStoriesResponse($extra_features['story'],!empty($extra_features['data_to_validate']) ? $extra_features['data_to_validate'] : []) : null;
            $response['is_following'] = !empty($extra_features['is_following']) ? $extra_features['is_following'] : false;
            $response['is_favourite'] = !empty($extra_features['is_favourite']) ? $extra_features['is_favourite'] : false;
            $response['onboard_count'] = !empty($data['onboard_count']) ? $data['onboard_count'] : null;
            $response['profile_type'] = !empty($data['profile_type']) ? $data['profile_type'] : 0;
            $response['skills'] = !empty($data['skills']) ? $data['skills'] : null;
            $response['boat_services'] = self::freelancerQualificationResponse(!empty($data['qualifications']) ? $data['qualifications'] : []);
            $response['freelancer_categories'] = self::freelancerCategoriesResponse(!empty($data['freelancer_categories']) ? $data['freelancer_categories'] : null);

            $response['locations'] = LoginHelper::processFreelancerLocationsResponse((!empty($data['one_location']) ? $data['one_location'] : []));
            $response['distance'] = self::getDistance((!empty($data['one_location']) ? $data['one_location'] : []), $inputs);
            $response['social_media'] = self::freelancerSocialMediaResponse((!empty($data['social_media']) ? $data['social_media'] : []));

            $response['subscription_settings'] = self::freelancerSubscription_settings((!empty($data['subscription_settings']) ? $data['subscription_settings'] : []));

            $response['can_travel'] = !empty($data['can_travel']) ? $data['can_travel'] : 0;
            $response['travelling_distance'] = !empty($data['travelling_distance']) ? $data['travelling_distance'] : null;
            $response['travelling_cost_per_km'] = !empty($data['travelling_cost_per_km']) ? $data['travelling_cost_per_km'] : null;
            //$response['travelling_cost_per_km'] = !empty($data['travelling_cost_per_km']) ? CommonHelper::getConvertedCurrency($data['travelling_cost_per_km'], $data['default_currency'], $to_currency)  : null;
            $response['is_business'] = !empty($data['is_business']) ? $data['is_business'] : 0;
            $response['business_name'] = !empty($data['business_name']) ? $data['business_name'] : null;
            $response['business_logo'] = !empty($data['business_logo']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['mobile_uploads'] . $data['business_logo'] : null;

            $response['purchase_subscription'] = !empty($data['subscriptions']) ? self::freelancerSubscription($data['subscriptions'], $local_timezone) : null;
            $response['cancel_subscription'] = !empty($data['subscriptions']) ? $data['subscriptions'][0]['auto_renew'] : 1;

            $response['average_rating'] = !empty($extra_features['reviews']) ? self::countRating($extra_features['reviews']) : self::countRating($data['reviews']);
            // $response['provides_online_services'] = $flag_online['is_online'];
            // $response['provides_face_to_face'] = $flag_online['is_face_to_face'];
            // $response['receive_subscription_request'] = $data['receive_subscription_request'];
            $response['currency'] = !empty($data['default_currency']) ? $data['default_currency'] : null;
            $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
            // $response['has_bank_detail'] = ($data['has_bank_detail'] == 1) ? true : false;
            $response['share_url'] = self::prepareShareProfileURL($data);
            $response['captains'] = self::CaptainProfileMapper($data);
            //  $response['added_subscription_once'] = SubscriptionHelper::checkFreelancerSubscriptionSettingExists($data);

            if (!empty($data['saved_category'])) {
                $response['freelancer_industry'] = LoginHelper::prepareIndustryResponse($data);
            }
            $ids['customer_id'] = ((isset($extra_features['customer_id'])) && (!empty($extra_features['customer_id']))) ? $extra_features['customer_id'] : null;
            $ids['freelancer_id'] = ((isset($data['id'])) && (!empty($data['id']))) ? $data['id'] : null;
            $chat_data = ClientHelper::getClientChatData($ids);
            if (!empty($chat_data)) {
                $response['has_subscribed'] = ($chat_data['has_subscription'] == 1) ? true : false;
                $response['has_appointment'] = ($chat_data['has_appointment'] == 1) ? true : false;
                //$response['has_followed'] = ($chat_data['has_followed'] == 1) ? true : false;
            }
            $response['weekly_schedules'] = ScheduleHelper::getWeeklySchedules($freelancer_id);
        }

        return $response;
    }

    public static function freelancerCoverSingleImageResponse($data = null) {
        // if freelance contain cover image return it
        if (!empty($data['cover_image'])) {
            return config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $data['cover_image'];
        }
        if (!empty($data['freelancer_categories'])) {
            // if freelancer does not contain cover image then get it from it's category
            if (!empty($data['freelancer_categories']['category'])) {
                if (!empty($data['freelancer_categories']['category']['image'])) {
                    return config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_category_image'] . $data['freelancer_categories']['category']['image'];
                }
            }
            // if freelancer category does not contain image then get it from sub category
            if (!empty($data['freelancer_categories']['sub_category'])) {
                return config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['category_image'] . $data['freelancer_categories']['sub_category']['image'];
            }
        }
        return null;
    }

    public static function getDistance($location, $inputs) {
        $distance = 0;
        if ($location && array_key_exists(0, $location) && (!empty($inputs['lat'])) && (!empty($inputs['lng']))) {
//            $distance = !empty($location[0]['location']) ? (!empty($location[0]['location']['distance']) ? $location[0]['location']['distance'] : null) : null;
            $lat = !empty($location[0]['location']['lat']) ? ($location[0]['location']['lat']) : null;
            $lng = !empty($location[0]['location']['lng']) ? ($location[0]['location']['lng']) : null;
            $distance = self::calculateDistance($inputs['lat'], $inputs['lng'], $lat, $lng);
        }
        return $distance;
    }

    public static function calculateDistance(
            $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
                pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        $distance = $angle * $earthRadius;
        return !empty($distance) ? (round(($distance / 1000), 2)) : 0;
    }

    public static function CaptainProfileMapper($data) {
        $records = [];
        if (array_key_exists('captains', $data) && $data['captains'] != null && $data['captains'] != "") {
            foreach ($data['captains'] as $captain) {
                $records[] = [
                    'captain_uuid' => $captain['captain_uuid'],
                    'captain_name' => $captain['captain_name'],
                    'is_active' => $captain['is_active'],
                    'captain_image' => self::freelancerProfileImagesResponse($captain['captain_image']),
                ];
            }
        }

        return $records;
    }

    public static function freelancerSubscription_settings($data = []) {

        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {

                $response[$key]['subscription_settings_uuid'] = (isset($value['subscription_settings_uuid'])) ? $value['subscription_settings_uuid'] : $value['subscription_settings_id'];
                $response[$key]['price'] = $value['price'];
                $response[$key]['currency'] = $value['currency'];
                $response[$key]['type'] = $value['type'];
            }
        }
        return $response;
    }

    public static function freelancerSubscriptionSettingsCurrency($data = [], $from_currency = 'SAR', $to_currency = 'Pound') {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $response[$key]['subscription_settings_uuid'] = $value['subscription_settings_uuid'];
                $response[$key]['price'] = !empty($value['price']) ? (double) CommonHelper::getConvertedCurrency($value['price'], $from_currency, $to_currency) : 0;
                $response[$key]['currency'] = $value['currency'];
                $response[$key]['type'] = $value['type'];
            }
        }
        return $response;
    }

    public static function freelancerSubscription($data = [], $local_timezone) {
        $response = null;
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $subscription_date = strtotime($value['subscription_date']);
                $sub_typ = $value['subscription_setting']['type'];

                if ($sub_typ == 'monthly') {
                    $subscription_date = strtotime("+1 month", $subscription_date);
                }
                if ($sub_typ == 'quarterly') {
                    $subscription_date = strtotime("+3 month", $subscription_date);
                }
                if ($sub_typ == 'annual') {
                    $subscription_date = strtotime("+12 month", $subscription_date);
                }

                if ($subscription_date > strtotime(date('Y-m-d H:i:s'))) {
                    $subscription_date = CommonHelper::convertDateTimeToTimezone($value['subscription_date'], 'UTC', $local_timezone);
                    $response['subscription_uuid'] = $value['subscription_uuid'];
                    $response['subscription_settings_uuid'] = $value['subscription_settings_id'];
                    $response['subscription_date'] = $subscription_date;
                    $response['price'] = $value['subscription_setting']['price'];
                    $response['currency'] = $value['subscription_setting']['currency'];
                    $response['type'] = $value['subscription_setting']['type'];
                }
            }
        }
        return $response;
    }

    public static function freelancerSocialMediaResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $response[$key]['social_media_uuid'] = $value['social_media_uuid'];
                $response[$key]['social_media_link'] = $value['social_media_link'];
                $response[$key]['social_media_type'] = $value['social_media_type'];
            }
        }
        return $response;
    }

    public static function freelancerQualificationResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $response[$key]['qualification_uuid'] = $value['qualification_uuid'];
                $response[$key]['title'] = $value['title'];
                $response[$key]['description'] = $value['description'];
            }
        }
        return $response;
    }

    public static function freelancerCategoriesResponse($data = []) {
        $response = [];

        if (!empty($data)) {
//            foreach ($data as $key => $value) {
//                $response[$key]['freelancer_category_uuid'] = $value['freelancer_category_uuid'];
//                $response[$key]['category_uuid'] = CommonHelper::getRecordByUuid('categories','id', $value['category_id'],'category_uuid');
//                $response[$key]['sub_category_uuid'] = CommonHelper::getRecordByUuid( 'sub_categories','id',$value['sub_category_id'],'sub_category_uuid');
//                $response[$key]['name'] = $value['name'];
//                $response[$key]['is_online'] = !empty($value['is_online']) ? $value['is_online'] : 0;
//            }

            $response['freelancer_category_uuid'] = $data['freelancer_category_uuid'];
            $response['category_uuid'] = CommonHelper::getRecordByUuid('categories', 'id', $data['category_id'], 'category_uuid');
            $response['sub_category_uuid'] = CommonHelper::getRecordByUuid('sub_categories', 'id', $data['sub_category_id'], 'sub_category_uuid');
            $response['name'] = $data['name'];
            $response['is_online'] = !empty($data['is_online']) ? $data['is_online'] : 0;
        }
        return $response;
    }

    public static function makeFreelancerSucscriptionArr($data = [], $to_currency = 'SAR') {
        $response = [];
        foreach ($data as $key => $val) {
            $response[] = array(
                'subscription_settings_uuid' => $val['subscription_settings_uuid'],
                'freelancer_uuid' => CommonHelper::getRecordByUuid('freelancers', 'id', $val['freelancer_id'], 'freelancer_uuid'),
                'type' => $val['type'],
                'price' => !empty($val['price']) ? (double) CommonHelper::getConvertedCurrency($val['price'], $val['currency'], $to_currency) : 0,
                'currency' => $val['currency']
            );
        }
        return $response;
    }

    public static function freelancerProfileResponseWithPost($data) {
        $response = [];
        if (!empty($data)) {
            $response['freelancer_uuid'] = $data['freelancer_uuid'];
            $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
            $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
            $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
            $response['company'] = !empty($data['company']) ? $data['company'] : null;
            $response['company_logo'] = !empty($data['company_logo']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['company_logo'] . $data['company_logo'] : null;
            $response['email'] = !empty($data['email']) ? $data['email'] : null;
            $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
            $response['country_code'] = !empty($data['country_code']) ? $data['country_code'] : null;
            $response['country_name'] = !empty($data['country_name']) ? $data['country_name'] : null;
//            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
            $response['profile_images'] = self::freelancerProfileImagesResponse($data['profile_image']);
            $response['cover_image'] = !empty($data['cover_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $data['cover_image'] : null;
            $response['cover_images'] = self::freelancerCoverImagesResponse($data['cover_image']);
            $response['cover_video'] = !empty($data['cover_video']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['cover_video'] . $data['cover_video'] : null;
            $response['cover_video_thumb'] = "http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5e985179c40311587040633.jpg";
            $response['gender'] = !empty($data['gender']) ? $data['gender'] : null;
            $response['bio'] = !empty($data['bio']) ? $data['bio'] : null;
            $response['has_story'] = true;
            $response['is_following'] = !empty($is_following) ? true : false;
            $response['onboard_count'] = !empty($data['onboard_count']) ? $data['onboard_count'] : null;
            $response['skills'] = !empty($data['skills']) ? $data['skills'] : null;
            $response['posts'] = PostResponseHelper::getMultiPostResponse($data);
        }
        return $response;
    }

    public static function prepareRecommendedUserResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['freelancer_uuid'] = $data['freelancer_uuid'];
            $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
            $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
            $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
            $response['reviews_count'] = count($data['reviews']);
            $response['average_rating'] = self::countRating($data['reviews']);
            //$response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
            $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['profile_image']);
        }
        return $response;
    }

    public static function countRating($data = []) {
        $response = 0;
        if (!empty($data)) {
            $rating = 0;
            $key = 0;
            foreach ($data as $key => $value) {
                $rating = ($rating + ((!empty($value['rating'])) ? $value['rating'] : 0));
            }
            $response = ($rating / ($key + 1));
        }
        return $response;
    }

    public static function prepareSuggestedProfilesResponse($suggestions = []) {
        $response = [];
        if (!empty($suggestions)) {
            foreach ($suggestions as $key => $value) {
                $response[$key]['freelancer_uuid'] = $value['freelancer_uuid'];
                $response[$key]['first_name'] = !empty($value['first_name']) ? $value['first_name'] : null;
                $response[$key]['last_name'] = !empty($value['last_name']) ? $value['last_name'] : null;
                $response[$key]['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($value['profession']) ? $value['profession'] : []);
                //$response[$key]['profile_image'] = !empty($value['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $value['profile_image'] : null;
                $response[$key]['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($value['profile_image']);
            }
        }
        return $response;
    }

    public static function prepareSubscribedProfilesResponse($subscriptions = []) {
        $response = [];
        if (!empty($subscriptions)) {
            foreach ($subscriptions as $key => $value) {
                $response[$key]['freelancer_uuid'] = $value['freelancer_uuid'];
                $response[$key]['first_name'] = !empty($value['first_name']) ? $value['first_name'] : null;
                $response[$key]['last_name'] = !empty($value['last_name']) ? $value['last_name'] : null;
                $response[$key]['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($value['profession']) ? $value['profession'] : []);
                //$response[$key]['profile_image'] = !empty($value['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $value['profile_image'] : null;
                $response[$key]['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($value['profile_image']);
            }
        }
        return $response;
    }

    public static function prepareReviewedProfilesResponse($reviews = []) {

        $response = [];
        if (!empty($reviews)) {
            foreach ($reviews as $key => $value) {
                $response[$key]['freelancer_uuid'] = $value['freelancer_uuid'];
                $response[$key]['first_name'] = !empty($value['first_name']) ? $value['first_name'] : null;
                $response[$key]['last_name'] = !empty($value['last_name']) ? $value['last_name'] : null;
                $response[$key]['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($value['profession']) ? $value['profession'] : []);
                //$response[$key]['profile_image'] = !empty($value['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $value['profile_image'] : null;
                $response[$key]['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($value['profile_image']);

                $response[$key]['review'] = !empty($value['latest_reviews']) ? self::freelancerReviewsProfileResponse($value['latest_reviews']) : null;

                $response[$key]['review_created_at'] = !empty($value['latest_reviews']) ? $value['latest_reviews'][0]['created_at'] : null;
            }
        }

        $name = 'review_created_at';
        usort($response, function ($a, $b) use (&$name) {
            return strtotime($b[$name]) - strtotime($a[$name]);
        });

        return $response;
    }

    public static function freelancerReviewsProfileResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            //foreach ($data as $key => $value) {
//                $response['review_uuid'] = $data['review_uuid'];
            $response['rating'] = !empty($data[0]['average_rating']) ? (double) $data[0]['average_rating'] : null;
            $response['review'] = !empty($data[0]['review']) ? $data[0]['review'] : null;
//                $response['review'] = !empty($data['review']) ? $data['review'] : null;
            //}
        }

        return $response;
    }

    public static function appointmentReviewResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            $response['uuid'] = !empty($data['appointment_uuid']) ? $data['appointment_uuid'] : null;
            $response['freelancer_uuid'] = !empty($data['appointment_freelancer']['freelancer_uuid']) ? $data['appointment_freelancer']['freelancer_uuid'] : null;
            $response['first_name'] = !empty($data['appointment_freelancer']['first_name']) ? $data['appointment_freelancer']['first_name'] : null;
            $response['last_name'] = !empty($data['appointment_freelancer']['last_name']) ? $data['appointment_freelancer']['last_name'] : null;
            $response['title'] = !empty($data['title']) ? $data['title'] : null;
            $response['type'] = !empty($data['type']) ? $data['type'] : null;
            $response['created_at'] = !empty($data['created_at']) ? $data['created_at'] : null;
            $time_con = CommonHelper::convertTimeToTimezone($data['from_time'], $data['saved_timezone'], $data['local_timezone']);
            $response['appointment_date'] = $data['appointment_date'] . ' ' . $time_con;
            $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['appointment_freelancer']['profile_image']);
        }
        return $response;
    }

    public static function appointmentReviewCustomerResponse($data = []) {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $response['uuid'] = !empty($value['review_uuid']) ? $value['review_uuid'] : null;
                $response['created_at'] = !empty($value['created_at']) ? $value['created_at'] : null;
                $response['first_name'] = !empty($value['customer']['first_name']) ? $value['customer']['first_name'] : null;
                $response['last_name'] = !empty($value['customer']['last_name']) ? $value['customer']['last_name'] : null;
                $response['title'] = !empty($value['appointment']['title']) ? $value['appointment']['title'] : null;
                $response['rating'] = !empty($value['rating']) ? $value['rating'] : 0;
                $response['review'] = !empty($value['review']) ? $value['review'] : null;
                $response['type'] = !empty($value['type']) ? $value['type'] : null;
                $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($value['customer']['profile_image']);
            }
        }
        return $response;
    }

    public static function freelancerProfileCurrencyResponse($data = [], $extra_features = [], $local_timezone = 'UTC', $to_currency = 'SAR', $login_user_type = null) {



        $response = [];

        if (!empty($data)) {

            $url = $_SERVER['HTTP_HOST'];
            $freelancer_categories = CategoryHelper::prepareSearchFreelancerCategoryResponse($data['freelancer_categories']);

            $flag_online = FreelancerSearchHelper::checkOnlineFaceToFaceFlag($freelancer_categories);

            $response['freelancer_uuid'] = $data['freelancer_uuid'];

            $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
            $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
            $response['company'] = !empty($data['company']) ? $data['company'] : null;
            $response['company_logo'] = !empty($data['company_logo']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['company_logo'] . $data['company_logo'] : null;
            $response['email'] = !empty($data['email']) ? $data['email'] : null;
            $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
            $response['country_code'] = !empty($data['country_code']) ? $data['country_code'] : null;
            $response['country_name'] = !empty($data['country_name']) ? $data['country_name'] : null;
//            $response['profile_image'] = !empty($data['profile_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_image'] : null;
            $response['profile_images'] = self::freelancerProfileImagesResponse($data['profile_image']);
            $response['profile_card_image'] = !empty($data['profile_card_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_profile_image'] . $data['profile_card_image'] : null;
            $response['cover_image'] = !empty($data['cover_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $data['cover_image'] : null;
            $response['cover_images'] = self::freelancerCoverImagesResponse($data['cover_image']);
            $response['cover_video'] = !empty($data['cover_video']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['cover_video'] . $data['cover_video'] : null;
            $response['cover_video_thumb'] = !empty($data['cover_video_thumb']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['cover_video'] . $data['cover_video_thumb'] : null;
//            $response['cover_video_thumb'] = "http://d2bp2kgc0vgu09.cloudfront.net/uploads/general/5e985179c40311587040633.jpg";
            $response['gender'] = !empty($data['gender']) ? $data['gender'] : null;
            $response['business_cover_image'] = (!empty($data['freelancer_categories']) && !empty($data['freelancer_categories'][0]['category']) && !empty($data['freelancer_categories'][0]['category']['image'])) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['category_image'] . $data['freelancer_categories'][0]['category']['image'] : null;
            $response['bio'] = !empty($data['bio']) ? $data['bio'] : null;
            $response['followers_count'] = !empty($extra_features['followers_count']) ? $extra_features['followers_count'] : 0;
            $response['reviews_count'] = !empty($extra_features['reviews_count']) ? $extra_features['reviews_count'] : 0;
            $response['reviews_avg'] = !empty($extra_features['reviews_avg']) ? $extra_features['reviews_avg'] : 0;
            $response['post_count'] = !empty($extra_features['post_count']) ? $extra_features['post_count'] : 0;
//            $response['has_story'] = true;

            $response['has_story'] = !empty($extra_features['story']) ? true : false;
            $response['stories'] = !empty($extra_features['story']) ? StoryResponseHelper::processStoriesResponse($extra_features['story'], $extra_features['data_to_validate'], $login_user_type) : null;

            $response['is_following'] = !empty($extra_features['is_following']) ? $extra_features['is_following'] : false;
            $response['is_favourite'] = !empty($extra_features['is_favourite']) ? $extra_features['is_favourite'] : false;
            $response['onboard_count'] = !empty($data['onboard_count']) ? $data['onboard_count'] : null;
            $response['profile_type'] = !empty($data['profile_type']) ? $data['profile_type'] : 0;
            $response['skills'] = !empty($data['skills']) ? $data['skills'] : null;

            $response['qualifications'] = self::freelancerQualificationResponse(!empty($data['qualifications']) ? $data['qualifications'] : []);

            $response['freelancer_categories'] = self::freelancerCategoriesResponse(!empty($data['freelancer_categories']) ? $data['freelancer_categories'] : null);

            $response['locations'] = LoginHelper::processFreelancerLocationsResponse((!empty($data['locations']) ? $data['locations'] : []));

            $response['social_media'] = self::freelancerSocialMediaResponse((!empty($data['social_media']) ? $data['social_media'] : []));
            $response['subscription_settings'] = !empty($data['subscription_settings']) ? self::freelancerSubscriptionSettingsCurrency($data['subscription_settings'], $data['default_currency'], $to_currency) : [];
            $response['can_travel'] = !empty($data['can_travel']) ? $data['can_travel'] : 0;
            $response['travelling_distance'] = !empty($data['travelling_distance']) ? $data['travelling_distance'] : null;
            //$response['travelling_cost_per_km'] = !empty($data['travelling_cost_per_km']) ? $data['travelling_cost_per_km'] : null;
            $response['travelling_cost_per_km'] = !empty($data['travelling_cost_per_km']) ? CommonHelper::getConvertedCurrency($data['travelling_cost_per_km'], $data['default_currency'], $to_currency) : null;
            $response['is_business'] = !empty($data['is_business']) ? $data['is_business'] : 0;
            $response['business_name'] = !empty($data['business_name']) ? $data['business_name'] : null;

            $response['business_logo'] = !empty($data['business_logo']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['mobile_uploads'] . $data['business_logo'] : null;
            $response['purchase_subscription'] = !empty($data['subscriptions']) ? self::freelancerSubscription($data['subscriptions'], $local_timezone) : null;
            $response['cancel_subscription'] = !empty($data['subscriptions']) ? $data['subscriptions'][0]['auto_renew'] : 1;
            $response['average_rating'] = !empty($extra_features['reviews']) ? self::countRating($extra_features['reviews']) : self::countRating($data['reviews']);
            $response['provides_online_services'] = $flag_online['is_online'];
            $response['provides_face_to_face'] = $flag_online['is_face_to_face'];
            $response['currency'] = !empty($data['default_currency']) ? $data['default_currency'] : null;
            //$response['public_chat'] = ($data['public_chat'] == 1) ? true : false;
            // $response['receive_subscription_request'] = $data['receive_subscription_request'];
            $response['profession_details'] = LoginHelper::processFreelancerProfessionResponse(!empty($data['profession']) ? $data['profession'] : []);
            $data_string = "freelancer_uuid=" . $data['freelancer_uuid'] . "&currency=" . $data['default_currency'];
            // $response['has_bank_detail'] = ($data['has_bank_detail'] == 1) ? true : false;
            $response['freelancer_industry'] = null;
            // $response['added_subscription_once'] = SubscriptionHelper::checkFreelancerSubscriptionSettingExists($data);
            if (!empty($data['saved_category'])) {
                $response['freelancer_industry'] = LoginHelper::prepareIndustryResponse($data);
            }

            $encoded_string = base64_encode($data_string);
            if ($login_user_type == "customer") {
                $response['has_subscription'] = $data['check_subscription'];
                $response['has_appointment'] = !empty($data['check_appointment']) ? $data['check_appointment'] : false;
            }
            if (strpos($url, 'localhost') !== false) {
                $response['share_url'] = "http://localhost/boatekapi/getFreelancerProfile" . "?" . $encoded_string;
            } elseif (strpos($url, 'staging') !== false) {
                $response['share_url'] = config("general.url.staging_url") . "getFreelancerProfile?" . $encoded_string;
//                $response['share_url'] = config("general.url.staging_url") . "getFreelancerProfile?freelancer_uuid=" . $data['freelancer_uuid'] . '&currency=' . $data['default_currency'];
            } elseif (strpos($url, 'dev') !== false) {
                $response['share_url'] = config("general.url.development_url") . "getFreelancerProfile?" . $encoded_string;
            } elseif (strpos($url, 'production') !== false) {
                $response['share_url'] = config("general.url.production_url") . "getFreelancerProfile?" . $encoded_string;
            }
        }

        return $response;
    }

    public static function prepareShareProfileURL($data) {
        $url = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "production";
        $share_url = "";
        if (!empty($data['freelancer_uuid'])) {
            $data['default_currency'] = !empty($data['default_currency']) ? $data['default_currency'] : "SAR";
            $data_string = "freelancer_uuid=" . $data['freelancer_uuid'] . "&currency=" . $data['default_currency'];
            $encoded_string = base64_encode($data_string);
            if (strpos($url, 'localhost') !== false) {
                $share_url = "http://localhost/boatekapi/getFreelancerProfile" . "?" . $encoded_string;
            } elseif (strpos($url, 'staging') !== false) {
                $share_url = config("general.url.staging_url") . "getFreelancerProfile?" . $encoded_string;
//                $share_url = config("general.url.staging_url") . "getFreelancerProfile?freelancer_uuid=" . $data['freelancer_uuid'] . '&currency=' . $data['default_currency'];
            } elseif (strpos($url, 'dev') !== false) {
                $share_url = config("general.url.development_url") . "getFreelancerProfile?" . $encoded_string;
            } elseif (strpos($url, 'production') !== false) {
                $share_url = config("general.url.production_url") . "getFreelancerProfile?" . $encoded_string;
            }
        }
        return $share_url;
    }

}

?>
