<?php

namespace App\Helpers;

Class PostDataHelper {
    /*
      |--------------------------------------------------------------------------
      | PostDataHelper that contains all the Post data methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use Post processes
      |
     */

    public static function makeReportPostArray($input) {
        $data = array(
            'reported_post_uuid' => UuidHelper::generateUniqueUUID(),
            'post_id' => !empty($input['post_id']) ? $input['post_id'] : null,
            'reporter_id' => !empty($input['reporter_id']) ? $input['reporter_id'] : null,
            'reported_type' => !empty($input['login_user_type']) ? $input['login_user_type'] : null,
            'comments' => !empty($input['comments']) ? $input['comments'] : null,
        );
        return $data;
    }

    public static function makeHideContentArray($input) {
        $data = array(
            'content_action_uuid' => UuidHelper::generateUniqueUUID(),
            'content_uuid' => !empty($input['content_uuid']) ? $input['content_uuid'] : null,
            'content_type' => !empty($input['content_type']) ? $input['content_type'] : null,
            'profile_uuid' => !empty($input['profile_uuid']) ? $input['profile_uuid'] : null,
            'is_hidden' => !empty($input['is_hidden']) ? $input['is_hidden'] : 0,
        );
        return $data;
    }

}

?>
