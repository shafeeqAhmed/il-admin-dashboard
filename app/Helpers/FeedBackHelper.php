<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\AppReview;

Class FeedBackHelper {
    /*
      |--------------------------------------------------------------------------
      | FeedBackHelper that contains all the posts like related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use post like processes
      |
     */

    public static function addFeedBack($inputs) {
        $validation = Validator::make($inputs, FeedBackValidationHelper::addFeedBackRules()['rules'], FeedBackValidationHelper::addFeedBackRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $feedback_data = self::makeFeedBackArray($inputs);
        $save_feedback = AppReview::saveFeedBack($feedback_data);
        if(empty($save_feedback)) {
            return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('error', $inputs['lang'])['success_error']);
        }
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData('success', $inputs['lang'])['successful_request']);
    }

    public static function makeFeedBackArray($input) {
        $userId = CommonHelper::getCutomerIdByUuid($input['profile_uuid'],'user_id');
        $data = array(
            'app_review_uuid' => UuidHelper::generateUniqueUUID(),
            'profile_uuid' => !empty($input['profile_uuid']) ? $input['profile_uuid'] : null,
            'type' => !empty($input['type']) ? $input['type'] : null,
            'comments' => !empty($input['comments']) ? $input['comments'] : null,
        );
        return $data;
    }
}

?>
