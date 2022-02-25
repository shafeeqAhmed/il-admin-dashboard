<?php

namespace App\Helpers;

Class FolderResponseHelper {
    /*
      |--------------------------------------------------------------------------
      | FolderResponseHelper that contains all the folder response methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use folder processes
      |
     */

    public static function prepareFolderResponse($data = [], $is_subscribed = false) {
        $response = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $response[$key]['folder_uuid'] = $value['folder_uuid'];
                $response[$key]['profile_uuid'] = CommonHelper::getRecordByUuid('freelancers','id',$value['freelancer_id'],'freelancer_uuid');
                $response[$key]['name'] = $value['name'];
                $response[$key]['type'] = $value['type'];
                $response[$key]['image'] = !empty($value['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['folder_images'] . $value['image'] : null;
                $response[$key]['is_subscribed'] = $is_subscribed;
            }
        }
        return $response;
    }

    public static function prepareFolderResponseForCustomer($data = [], $is_subscribed = false) {
        $response = [];
        if (!empty($data)) {
            $key = 0;
            foreach ($data as $count => $value) {
                if (!empty($value['single_post'])) {
                    $response[$key]['folder_uuid'] = $value['folder_uuid'];
                    $response[$key]['profile_uuid'] = $value['profile_uuid'];
                    $response[$key]['name'] = $value['name'];
                    $response[$key]['type'] = $value['type'];
                    $response[$key]['image'] = !empty($value['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['folder_images'] . $value['image'] : null;
                    $response[$key]['is_subscribed'] = $is_subscribed;
                    $key++;
                }
            }
        }
        return array_values($response);
    }

    public static function prepareSingleFolderResponse($value = []) {
        $response = [];
        if (!empty($value)) {

            $response['folder_uuid'] = $value['folder_uuid'];
            $response['profile_uuid'] = CommonHelper::getRecordByUuid('freelancers','id',$value['freelancer_id'],'freelancer_uuid');
            $response['name'] = $value['name'];
            $response['image'] = !empty($value['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['folder_images'] . $value['image'] : null;
        }
        return $response;
    }

}

?>
