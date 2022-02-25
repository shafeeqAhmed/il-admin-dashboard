<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Freelancer;
use App\Folder;

Class FolderHelper {
    /*
      |--------------------------------------------------------------------------
      | FolderHelper that contains all the folders related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use folder processes
      |
     */

    public static function addFolder($inputs) {
        $validation = Validator::make($inputs, FolderValidationHelper::addFolderRules()['rules'], FolderValidationHelper::addFolderRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $free_folder = true;
        if (!empty($inputs['image'])) {
            MediaUploadHelper::moveSingleS3Image($inputs['image'], CommonHelper::$s3_image_paths['folder_images']);
        }

        $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid($inputs['profile_uuid']);

        $check_folder = Folder::getFolders('freelancer_id', $inputs['freelancer_id']);
        if (empty($check_folder)) {
            $folder_data = ['freelancer_id' => $inputs['freelancer_id'], 'image' => null, 'name' => 'Free', 'type' => 'unpaid'];
            $free_folder = Folder::saveFolder($folder_data);
        }
        $data = ['freelancer_id' => $inputs['freelancer_id'], 'image' => !empty($inputs['image']) ? $inputs['image'] : null, 'name' => $inputs['name'], 'type' => 'paid'];
        $folder = Folder::saveFolder($data);
        if (!$folder || !$free_folder) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData($type = 'error', $inputs['lang'])['add_folder_error']);
        }
        $response = FolderResponseHelper::prepareSingleFolderResponse($folder);
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request'], $response);
    }

    public static function getFolders($inputs = []) {
        $validation = Validator::make($inputs, FolderValidationHelper::getFolderRules()['rules'], FolderValidationHelper::getFolderRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $inputs['freelancer_id'] = CommonHelper::getFreelancerIdByUuid($inputs['profile_uuid']);

        $folders_data = Folder::getFolders('freelancer_id', $inputs['freelancer_id']);
        $response = FolderResponseHelper::prepareFolderResponse($folders_data);
        return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['successful_request'], $response);
    }

    public static function updateFolder($inputs) {
        $validation = Validator::make($inputs, FolderValidationHelper::updateFolderRules()['rules'], FolderValidationHelper::updateFolderRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        if (!empty($inputs['image'])) {
            MediaUploadHelper::moveSingleS3Image($inputs['image'], CommonHelper::$s3_image_paths['folder_images']);
        }
        $data = [];
        if(!empty($inputs['image'])) {
            $data['image'] =  $inputs['image'];
        }
        if(!empty($inputs['name'])) {
            $data['name'] =  $inputs['name'];
        }
        $folder = Folder::updateFolder('folder_uuid',$inputs['folder_uuid'],$data);
        if (!$folder) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData($type = 'error', $inputs['lang'])['add_folder_error']);
        }
        DB::commit();
        $folder = Folder::getFolder('folder_uuid', $inputs['folder_uuid']);
        $response = FolderResponseHelper::prepareSingleFolderResponse($folder);
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request'], $response);
    }

    public static function deleteFolder($inputs) {
        $validation = Validator::make($inputs, FolderValidationHelper::deleteFolderRules()['rules'], FolderValidationHelper::deleteFolderRules()['message_' . strtolower($inputs['lang'])]);
        if ($validation->fails()) {
            return CommonHelper::jsonErrorResponse($validation->errors()->first());
        }
        $data = ['is_archive' => 1];
        $folder = Folder::updateFolder('folder_uuid',$inputs['folder_uuid'],$data);
        if (!$folder) {
            DB::rollback();
            return CommonHelper::jsonErrorResponse(MessageHelper::getMessageData($type = 'error', $inputs['lang'])['add_folder_error']);
        }
        DB::commit();
        return CommonHelper::jsonSuccessResponse(MessageHelper::getMessageData($type = 'success', $inputs['lang'])['successful_request']);
    }

}

?>
