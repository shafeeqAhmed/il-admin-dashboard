<?php

namespace App\Helpers;

use App\Customer;
use App\Favourite;
use App\NotificationSetting;
use App\User;
use App\WalkinCustomer;
use App\Freelancer;
use App\Location;
use App\Like;
use App\FreelancerLocation;
use App\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CustomerUpdateValidationHelper;
use App\Helpers\CustomerMediaHelper;
use DB;
use App\BookMark;
use App\Interest;
use App\Appointment;
use App\Notification;
use App\ClassBooking;
use App\ClassSchedule;
use App\Http\Controllers\ChatController;

Class CustomerHelper {

    public static function getCustomerList($inputs = []) {
        $customers = Customer::getAllCustomers();
        $response = CustomerResponseHelper::customerListResponse($customers);
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function customerSignup($inputs = []) {
        $validation = Validator::make($inputs, CustomerValidationHelper::customerSignupRules()['rules'], CustomerValidationHelper::customerSignupRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }

        $inputs['user_uuid'] = UuidHelper::generateUniqueUUID("users", "user_uuid");

        if (empty($inputs['facebook_id']) && empty($inputs['google_id']) && empty($inputs['apple_id'])) {
            if (empty($inputs['password'])) {
                return CommonHelper::jsonErrorResponse(CustomerValidationHelper::customerSignupRules()['message_' . strtolower($inputs['lang'])]['missing_password']);
            }
            $inputs['password'] = CommonHelper::convertToHash($inputs['password']);
        } else {
            if (isset($inputs['password']) && !empty($inputs['password'])) {
                $inputs['password'] = CommonHelper::convertToHash($inputs['password']);
            }
        }

        $inputs['is_active'] = 1;
        $inputs['public_chat'] = 1;
        $inputs['default_currency'] = $inputs['currency'];

        $already_exist = User::checkUserExistByPhone($inputs['phone_number']);
        if ($already_exist) {
            return CommonHelper::jsonErrorResponse("Phone number already exist");
        }

//        $save_user = User::saveUser();
        $userStoreResponse = UserHelper::makeUserStoreResponse($inputs);
        $save_user = User::addUser($userStoreResponse);
        $inputs['user_id'] = $save_user['id'];
        if (!$save_user) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }

        $customer['customer_uuid'] = UuidHelper::generateUniqueUUID("customers", "customer_uuid");
        $customer['user_id'] = $save_user['id'];
        $save_customer = Customer::saveCustomer($customer);

        if (!$save_customer) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }

        //$inputs['profile_uuid'] = $inputs['customer_uuid'];
//        $inputs['profile_id'] = $save_user['id'];

        $data = ['user_id' => $inputs['user_id'], 'new_appointment' => 1, 'cancellation' => 1, 'no_show' => 1, 'new_follower' => 1];

        $add_settings = NotificationSetting::addSetting($data);

        $inputs['device'] = ['device_type' => (!empty($inputs['device_type'])) ? $inputs['device_type'] : '', 'device_token' => (!empty($inputs['device_token'])) ? $inputs['device_token'] : ''];

        $update_device = LoginHelper::updateDeviceData($inputs['device'], $inputs);

//        $save_customer['type'] = "customer";
//        $create_admin_chat = ChatController::createAdminChat($save_customer);
        if (!$update_device || !$add_settings) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }
        DB::commit();

        $user = User::getUserDetail('id', $save_user['id']);
        $response = UserResponseHelper::prepareSignupResponse($user);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function customerSocialSignp($inputs = []) {
        $inputs['customer_uuid'] = UuidHelper::generateUniqueUUID("customers", "customer_uuid");
        if (empty($inputs['first_name'])) {
            if (!empty($inputs['email'])) {
                $mail_parts = explode("@", $inputs['email']);
                $inputs['first_name'] = $mail_parts[0];
                $inputs['last_name'] = "Customer";
            } else {
                $uuid_parts = explode("-", $inputs['customer_uuid']);
                $inputs['first_name'] = "Customer";
                $inputs['last_name'] = $uuid_parts[0];
            }
        }
        $validation = Validator::make($inputs, CustomerValidationHelper::customerSocialSignupRules()['rules'], CustomerValidationHelper::customerSocialSignupRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['default_currency'] = !empty($inputs['currency']) ? $inputs['currency'] : "SAR";
        $inputs['is_active'] = 1;
        $inputs['onboard_count'] = 2;
        $save_customer = Customer::saveCustomer($inputs);
        if (!$save_customer) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }
        return self::customerSocialSignpProcess($inputs);
    }

    public static function customerSocialSignpProcess($inputs = []) {
        $inputs['profile_uuid'] = $inputs['customer_uuid'];
        $data = ['profile_uuid' => $inputs['profile_uuid'], 'new_appointment' => 1, 'cancellation' => 1, 'no_show' => 1, 'new_follower' => 1];
        $add_settings = NotificationSetting::addSetting($data);
        $inputs['device'] = ['device_type' => (!empty($inputs['device_type'])) ? $inputs['device_type'] : '', 'device_token' => (!empty($inputs['device_token'])) ? $inputs['device_token'] : ''];
        $update_device = LoginHelper::updateDeviceData($inputs['device'], $inputs['customer_uuid']);
        if (!$update_device || !$add_settings) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }
        $single_customer = Customer::getSingleCustomer('customer_uuid', $inputs['customer_uuid']);
        $single_customer['type'] = "customer";
        $create_chat = ChatController::createAdminChat($single_customer);
        $response = CustomerResponseHelper::prepareLoginResponse($single_customer);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function getCustomerHomeFeed($inputs = []) {
        $validation = Validator::make($inputs, CustomerValidationHelper::getCustomerFeedRules()['rules'], CustomerValidationHelper::getCustomerFeedRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $sort_array = [];
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : 0;
        $limit = !empty($inputs['limit']) ? $inputs['limit'] : 10;
//        $followings = Follower::getCustomerFollowingStories('follower_uuid', $inputs['customer_uuid']);
        $stories = Freelancer::getCustomerFeedStories('is_archive', 0);
        $recommended = Freelancer::getRecommendedProfile('is_archive', 0);
        $suggestions = Freelancer::getSuggestedProfiles('is_archive', 0, 0, 10);
//        $response['stories'] = FollowerDataHelper::followersResponse($followings);
        $sort_array['stories'] = StoryResponseHelper::prepareFeedStoriesResponse($stories);
        $response['stories'] = self::sortStoryProfiles($sort_array['stories']);
        $response['posts'] = self::preparePostResponse($inputs, $limit, $offset);
        $response['new_professional'] = FreelancerResponseHelper::prepareRecommendedUserResponse($recommended);
        $response['suggestions'] = FreelancerResponseHelper::prepareSuggestedProfilesResponse($suggestions);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function preparePostResponse($inputs = [], $limit = null, $offset = null) {
        $posts_response = [];
        $posts = Post::getPublicFeedProfilePosts('is_archive', 0, $limit, $offset);
        if (!empty($posts)) {
            foreach ($posts as $key => $post) {
                $liked_by_users_ids = [];
                if (!empty($post['likes'])) {
                    foreach ($post['likes'] as $like) {
                        array_push($liked_by_users_ids, $like['liked_by_uuid']);
                    }
                }
                $likes_count = Like::getLikeCount('post_uuid', $post['post_uuid']);
                $bookmarked_ids = BookMark::getBookMarkedPostIds('customer_uuid', $inputs['logged_in_uuid']);
                $data_to_validate = ['liked_by_users_ids' => $liked_by_users_ids, 'bookmarked_ids' => $bookmarked_ids, 'likes_count' => $likes_count];
                $posts_response[$key] = PostResponseHelper::prepareCustomerFeedPostResponse($post, $inputs['logged_in_uuid'], $data_to_validate);
            }
        }
        return $posts_response;
    }

    public static function getCustomerHomeFeedForGuest($inputs = []) {
        $validation = Validator::make($inputs, CustomerValidationHelper::getCustomerFeedRules()['rules'], CustomerValidationHelper::getCustomerFeedRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : 0;
        $limit = !empty($inputs['limit']) ? $inputs['limit'] : 10;
        $search_data['lat'] = $inputs['lat'];
        $search_data['lng'] = $inputs['lng'];
        $locations = Location::getProfileAddress($search_data);
        $location_uuids = [];
        foreach ($locations as $key => $location) {
            $location_data[$key]['address'] = $location->address;
            $location_data[$key]['route'] = $location->route;
            $location_data[$key]['street_number'] = $location->street_number;
            $location_data[$key]['city'] = $location->city;
            $location_data[$key]['state'] = $location->state;
            $location_data[$key]['country'] = $location->country;
            $location_data[$key]['country_code'] = $location->country_code;
            $location_data[$key]['zip_code'] = $location->zip_code;
            $location_data[$key]['location_id'] = $location->location_id;
            $location_data[$key]['location_uuid'] = $location->location_uuid;
            $location_data[$key]['lat'] = $location->lat;
            $location_data[$key]['lng'] = $location->lng;
            $location_data[$key]['distance'] = $location->distance;
            if (!in_array($location->location_uuid, $location_uuids)) {
                array_push($location_uuids, $location->location_uuid);
            }
        }
        $profile_addresses = FreelancerLocation::getProfileAddresses($location_uuids, 20, 0);
        $recommended = Freelancer::getRecommendedProfile('is_archive', 0);
        $suggestions = Freelancer::getSuggestedProfiles('is_archive', 0, 0, 10);
        $posts = Post::getGuestProfilePosts('is_archive', 0, $limit, $offset);
        $stories = Freelancer::getCustomerFeedStories('is_archive', 0);
        $sort_array['stories'] = StoryResponseHelper::prepareFeedStoriesResponse($stories);
        $response['stories'] = self::sortStoryProfiles($sort_array['stories']);
//        $response['stories'] = FollowerDataHelper::suggestionsResponse($profile_addresses);
        $response['posts'] = PostResponseHelper::preparePostResponse($posts);
        $response['new_professional'] = FreelancerResponseHelper::prepareRecommendedUserResponse($recommended);
        $response['suggestions'] = FreelancerResponseHelper::prepareSuggestedProfilesResponse($suggestions);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function sortStoryProfiles($response) {
        if (!empty($response)) {
            usort($response, function ($b, $a) {
                return strcmp($a["timestamp"], $b["timestamp"]);
            });
        }
        return !empty($response) ? $response : [];
    }

    public static function updateCustomerProcess($inputs = []) {

        $save_profile = self::updateCustomer($inputs);
        $result = json_decode(json_encode($save_profile));
        if (!$result->original->success) {
            DB::rollBack();
//            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
            return CommonHelper::jsonErrorResponse($result->original->message);
        }
        $customer = Customer::getCustomerDetail('customer_uuid', $inputs['customer_uuid']);
        $response = CustomerResponseHelper::updateCustomerListResponse($customer);
        return CommonHelper::jsonSuccessResponse(CustomerMessageHelper::getMessageData('success', $inputs['lang'])['update_success'], $response[0]);
    }

    public static function updateCustomer($inputs = []) {
        $update_customer = true;
        $validation = Validator::make($inputs, CustomerUpdateValidationHelper::updateProfileRules()['rules'], CustomerUpdateValidationHelper::updateProfileRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $check_customer = Customer::getSingleCustomerDetail('customer_uuid', $inputs['customer_uuid']);
        if (empty($check_customer)) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
        }
        $customer_inputs_data = CustomerUpdateValidationHelper::processCustomerInputs($inputs, $check_customer);
        if (!empty($customer_inputs_data['data']['onboard_count'])) {
            $update_customer = Customer::updateCustomer('customer_uuid', $inputs['customer_uuid'], ['onboard_count' => $customer_inputs_data['data']['onboard_count']]);
        }
        if (!$customer_inputs_data['success']) {
            return CommonHelper::jsonErrorResponse($customer_inputs_data['message']);
        }
        $customer_inputs = $customer_inputs_data['data'];
        $process_images = CustomerMediaHelper::customerProfileMediaProcess($customer_inputs, $inputs);
        if (!$process_images['success']) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
        }
        if (!empty($inputs['profile_image'])) {
            $result = ThumbnailHelper::processThumbnails($inputs['profile_image'], 'profile_image', 'customer');
            if (!$result['success']) {
//                return CommonHelper::jsonErrorResponse($result['data']['errorMessage']);
                return CommonHelper::jsonErrorResponse("Profile image could not be updated");
            }
        }
        if (!empty($inputs['cover_image'])) {
            $result = ThumbnailHelper::processThumbnails($inputs['cover_image'], 'cover_image', 'customer');
            if (!$result['success']) {
//                return CommonHelper::jsonErrorResponse($result['data']['errorMessage']);
                return CommonHelper::jsonErrorResponse("Cover image could not be updated");
            }
        }
        $customer_update_inputs = $process_images['response'];
//        $update = Customer::updateCustomer('customer_uuid', $inputs['customer_uuid'], $customer_update_inputs);
        unset($customer_update_inputs['customer_uuid']);
        unset($customer_update_inputs['onboard_count']);
        $update = User::updateUser('id', $check_customer['user_id'], $customer_update_inputs);
        if (!$update || !$update_customer) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
        }
        DB::commit();
        return CommonHelper::jsonSuccessResponse(CustomerMessageHelper::getMessageData('success', $inputs['lang'])['update_success']);
    }

    public static function updateCustomerPhone($customer_inputs, $inputs) {
        $validation = Validator::make($inputs, CustomerUpdateValidationHelper::customerPhoneUpdateRules()['rules'], CustomerUpdateValidationHelper::customerPhoneUpdateRules()['message_' . strtolower($inputs['lang'])]
        );
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $customer_inputs['phone_number'] = $inputs['phone_number'];
        $customer_inputs['country_code'] = $inputs['country_code'];
        $customer_inputs['country_name'] = $inputs['country_name'];
        return $customer_inputs;
    }

    public static function addInterests($inputs = []) {
        $customer_data = [];
        $validation = Validator::make($inputs, CustomerValidationHelper::addInterestsRules()['rules'], CustomerValidationHelper::addInterestsRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }

        $interest_check = Interest::getCustomerInterest('customer_id', CommonHelper::getCutomerIdByUuid($inputs['customer_uuid']));

        if ($interest_check) {
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['interest_exit_error']);
        }

        if (empty($inputs['category_uuid'])) {
            return CommonHelper::jsonErrorResponse("Invalid interest data provided");
        }

        $data = CustomerDataHelper::makeAddInterestArray($inputs);

        $result = Interest::saveMultipleInterests($data);

        $customer_data['customer_uuid'] = $inputs['customer_uuid'];
        if (!empty($inputs['onboard_count'])) {
            $customer_data['onboard_count'] = $inputs['onboard_count'];
        }

        $update = Customer::updateCustomer('customer_uuid', $inputs['customer_uuid'], $customer_data);
        if (!$result && !$update) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['save_interest_error']);
        }
        DB::commit();
        $customer = Customer::getCustomerDetail('customer_uuid', $inputs['customer_uuid']);

        $response = CustomerResponseHelper::updateCustomerListResponse($customer);
        return CommonHelper::jsonSuccessResponse(CustomerMessageHelper::getMessageData('success', $inputs['lang'])['save_interest_success'], $response);
    }

    public static function getCustomerDashboard($inputs = []) {
        $validation = Validator::make($inputs, CustomerValidationHelper::getDashboardRules()['rules'], CustomerValidationHelper::getDashboardRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $limit = !empty($inputs['limit']) ? $inputs['limit'] : null;
        $offset = !empty($inputs['offset']) ? $inputs['offset'] : null;

        $customer = Customer::getSingleCustomerDetail('customer_uuid', $inputs['customer_uuid']);
        $inputs['customer_id'] = $customer['id'];
//        if (empty($customer)) {
//            $customer = WalkinCustomer::getCustomer('walkin_customer_uuid', $inputs['customer_uuid']);
//            if (!empty($customer)) {
//                $customer['customer_uuid'] = $inputs['customer_uuid'];
//            }
//        }
        if (empty($customer)) {
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData('error', $inputs['lang'])['invalid_data']);
        }

        $response['counts'] = self::getCustomerDashboardcounts($inputs);

        $type = ['notification_type' => 'other'];
        $search_params['status'] = isset($inputs['status']) ? $inputs['status'] ?? '' : '';
//        $update_notification = Notification::updateNotificationCount('receiver_uuid', $inputs['customer_uuid'], $type);

        $all_appointments = Appointment::getUpcomingAppointments('customer_id', $inputs['customer_id'], $limit, $offset);

        $upcomming_appointment = AppointmentResponseHelper::upcomingAppointmentsResponse($all_appointments, $inputs['local_timezone']);

//        $pending_booked_classes = ClassBooking::pluckClassBookingIds('customer_id', $inputs['customer_id'], 'class_schedule_id', 'pending');
//
//        $get_pending_schedules = ClassSchedule::getMultipleSchedules($pending_booked_classes, 'pending');
//
//        $pending_upcomming_classes = AppointmentResponseHelper::customerUpcomingClassAppointmentsResponse($get_pending_schedules, $inputs['local_timezone'], $customer, 'pending');
//
//        $confirmed_booked_classes = ClassBooking::pluckClassBookingIds('customer_id', $inputs['customer_id'], 'class_schedule_id', 'confirmed');
//
//        $get_confirmed_schedules = ClassSchedule::getMultipleSchedules($confirmed_booked_classes, 'confirmed');
//
//        $confirmed_upcomming_classes = AppointmentResponseHelper::customerUpcomingClassAppointmentsResponse($get_confirmed_schedules, $inputs['local_timezone'], $customer, 'confirmed');
        // dd($confirmed_upcomming_classes);
        if (empty($pending_upcomming_classes)) {
            $pending_upcomming_classes = [];
        }
        if (empty($confirmed_upcomming_classes)) {
            $confirmed_upcomming_classes = [];
        }
        $upcomming_classes = array_merge($pending_upcomming_classes, $confirmed_upcomming_classes);

        $response['all_appointments'] = $upcomming_appointment;
        if (empty($upcomming_appointment) || $upcomming_appointment == null) {
            $upcomming_appointment = [];
        }
        if (empty($upcomming_classes) || $upcomming_classes == null) {
            $upcomming_classes = [];
        }
        $response['all_appointments'] = array_merge($upcomming_appointment, $upcomming_classes);
        if (empty($response['all_appointments'])) {
            $response['all_appointments'] = [];
        }
        usort($response['all_appointments'], function ($a, $b) {
            $t1 = strtotime($a['datetime']);
            $t2 = strtotime($b['datetime']);
            return $t1 - $t2;
        });
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function makeCustomerAppointmentResponse($appointments) {
        foreach ($appointments as &$appointment) {
            $start = new Carbon(date('Y-m-d H:i:s', $appointment['appointment_start_date_time']));
            $end = new Carbon(date('Y-m-d H:i:s', $appointment['appointment_end_date_time']));
            $appointment['duration'] = $start->diffInDays($end) . ' Days ' . $start->diffInHours($end) . ' Hours ' . $start->diffInMinutes($end) . ' Minutes';
            $appointment['customer'] = $appointment['appointment_customer'];
            $appointment['freelancer'] = $appointment['appointment_freelancer'];
            $appointment['customer']['dob'] = $appointment['appointment_customer']['user']['dob'];
            unset($appointment['appointment_customer']);
            unset($appointment['appointment_freelancer']);
        }
        return $appointments;
    }

    public static function getCustomerDashboardcounts($inputs) {
        $data = [];

        $all_appointment_count = Appointment::getCustomerAppointmentsCount($inputs['customer_id'], [], 'current');

        $confirmed_appointment_count = Appointment::getCustomerAppointmentsCount($inputs['customer_id'], ['confirmed'], 'current');

        $data[] = ['title' => 'Total Bookings', 'count' => ($all_appointment_count)];

//        $data[] = ['title' => 'confirmed', 'count' => ($confirmed_appointment_count)];
//
//        $data[] = ['title' => 'total_booking', 'count' => ($pending_appointment_count+$confirmed_appointment_count)];
        $data[] = ['title' => 'Favorite Boats', 'count' => Favourite::getFavoriteBoatCount('customer_id', $inputs['customer_id'])];

//        $data[] = ['title' => 'notifications', 'count' => Notification::getNotificationCount('receiver_id', $inputs['customer_id'])];

        return $data;
    }

    public static function createAdminCustomer($inputs = []) {
//        $inputs['user_uuid'] = UuidHelper::generateUniqueUUID("users", "user_uuid");
        if (empty($inputs['facebook_id']) && empty($inputs['google_id']) && empty($inputs['apple_id'])) {
            if (empty($inputs['password'])) {
                return CommonHelper::jsonErrorResponse(CustomerValidationHelper::customerSignupRules()['message_' . strtolower($inputs['lang'])]['missing_password']);
            }
            $inputs['password'] = CommonHelper::convertToHash($inputs['password']);
        }
        $inputs['password'] = CommonHelper::convertToHash($inputs['password']);
        $inputs['is_active'] = 1;
        $save_customer = User::addUser($inputs);
        if (!$save_customer) {
            DB::rollBack();
            return CommonHelper::jsonErrorResponse(CustomerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
        }
//        $inputs['profile_uuid'] = $inputs['user_uuid'];
//        $data = ['profile_uuid' => $inputs['profile_uuid'], 'new_appointment' => 1, 'cancellation' => 1, 'no_show' => 1, 'new_follower' => 1];
//        $add_settings = NotificationSetting::addSetting($data);
//        $inputs['device'] = ['device_type' => (!empty($inputs['device_type'])) ? $inputs['device_type'] : '', 'device_token' => (!empty($inputs['device_token'])) ? $inputs['device_token'] : ''];
//        $update_device = LoginHelper::updateDeviceData($inputs['device'], $inputs['customer_uuid']);
//        if (!$update_device || !$add_settings) {
//            DB::rollBack();
//            return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['signup_error']);
//        }
        DB::commit();
        $response['user_uuid'] = $save_customer['user_uuid'];
//        $response['first_name'] = $save_customer['first_name'];
//        $response['last_name'] = $save_customer['last_name'];
//        $response['type'] = $save_customer['type'];
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

}

?>
