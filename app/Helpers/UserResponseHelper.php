<?php

namespace App\Helpers;

use App\Freelancer;

class UserResponseHelper {

    public static function prepareSignupResponse($data = []) {
        \Log::info("data");
        \Log::info($data);
        $response = [];
        if ($data) {
            $response['user_uuid'] = $data['user_uuid'];
            $response['first_name'] = !empty($data['first_name']) ? $data['first_name'] : null;
            $response['last_name'] = !empty($data['last_name']) ? $data['last_name'] : null;
            $response['email'] = !empty($data['email']) ? $data['email'] : null;
            $response['profile_type'] = 1;
            $response['type'] = $data['profile_type'];
            $response['phone_number'] = !empty($data['phone_number']) ? $data['phone_number'] : null;
            $response['country_code'] = !empty($data['country_code']) ? $data['country_code'] : null;
            $response['country_name'] = !empty($data['country_name']) ? $data['country_name'] : null;
            if ($data['profile_type'] == "customer") {
                $response['profile_images'] = CustomerResponseHelper::customerProfileImagesResponse($data['profile_image']);
            } else {
                $response['profile_images'] = FreelancerResponseHelper::freelancerProfileImagesResponse($data['profile_image']);
            }
            $response['cover_image'] = !empty($data['cover_image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_cover_image'] . $data['cover_image'] : null;
            $response['onboard_count'] = !empty($data['user_customer']['onboard_count']) ? $data['user_customer']['onboard_count'] : null;
            $response['currency'] = !empty($data['default_currency']) ? $data['default_currency'] : null;
            $response['has_boats'] = ($data['freelancers_count'] > 0) ? true : false;
//            $response['has_boats'] = self::hasAppointment($data['id'],$data['profile_type']);
            $response['has_bank_detail'] = ($data['has_bank_detail'] == 1) ? true : false;
            $response['customer_uuid'] = (isset($data['user_customer']['id'])) ? $data['user_customer']['customer_uuid'] : null;
            $response['freelancer_uuid'] = (isset($data['user_freelancer']['id'])) ? $data['user_freelancer']['freelancer_uuid'] : null;
            $response['location'] = [
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng']
            ];
        }
        return $response;
    }

//prepareLocationResponse
    public static function makeUserUpdateAttributes($data = []) {
        $response = [];
        $userAttributes = ['first_name', 'last_name', 'country_code', 'country_name',
            'profile_image', 'profile_card_image', 'cover_video', 'cover_video_thumb', 'has_bank_detail',
            'cover_image', 'facebook_id', 'google_id', 'apple_id',
            'default_currency'];

        foreach ($data as $key => $value) {
            if (in_array($key, $userAttributes) && $value) {
                $response[$key] = $value;
            }
        }

        return $response;
    }

    public static function hasAppointment($userId, $type) {

        $check = false;
        $appointmentCheck = Freelancer::getFreelancerBoats('user_id', $userId);

        if (!empty($appointmentCheck) && sizeof($appointmentCheck) > 0) {
            $check = true;
        }
        return $check;
    }

}
