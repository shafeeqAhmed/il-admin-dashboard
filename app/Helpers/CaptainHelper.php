<?php

namespace App\Helpers;

use App\Captain;
use App\MoyasarWebForm;
use App\Package;
use App\PaymentDue;
use App\RefundTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Appointment;
use App\Classes;
use App\BlockedTime;
use App\Schedule;
use App\FreelancerTransaction;
use App\Freelancer;
use App\Customer;
use App\WalkinCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

Class CaptainHelper {

    public static function CaptainRequest($inputs) {
        if (!isset($inputs['onboard_count'])) {
            self::deleteCaptain($inputs);
        }
        $record = self::addCaptain($inputs);
        if ($record['res'] == true) {
            return self::sendFreelancerResponse($inputs);
        } else {
            return $record['data'];
        }
    }

    public static function addCaptain($inputs) {

        $records = [];
        if ((!isset($inputs['onboard_count'])) || ($inputs['onboard_count'] == 4)) {

            $frelancerId = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']);

            if (isset($inputs['captain'])) {

                foreach ($inputs['captain'] as $captain) {
                    $validation = Validator::make($captain, CategoryValidationHelper::saveCaptionRules()['rules'], CategoryValidationHelper::saveCaptionRules()['message_' . strtolower($inputs['lang'])]);
                    if ($validation->fails()) {
                        return CommonHelper::jsonErrorResponse($validation->errors()->first());
                    }

                    if (!empty($captain['image'])) {

                        $result = self::processImage($captain);

                        if (!$result['success']) {
                            return ['res' => false, 'data' => CommonHelper::jsonErrorResponse("Profile image could not be processed")];
                        }
                    }

                    $records[] = [
                        'captain_uuid' => UuidHelper::generateUniqueUUID("captain_profile", "captain_uuid"),
                        'captain_name' => $captain['name'],
                        // 'captain_image' => self::processImage($captain),
                        'captain_image' => (!empty($captain['image'])) ? $captain['image'] : null,
                        'freelancer_id' => $frelancerId
                    ];
                }
                if (!empty($records)) {
                    Captain::createCaptain($records);
                }
            }
        }
        return ['res' => true];
    }

    public static function processImage($inputs) {
        MediaUploadHelper::moveSingleS3Image($inputs['image'], CommonHelper::$s3_image_paths['freelancer_profile_image']);
        $result = ThumbnailHelper::processThumbnails($inputs['image'], 'profile_image', 'freelancer');

        return $result;
    }

    public static function deleteCaptain($inputs) {
        $record = ['is_active' => 1];
        $freelancer_id = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']);
        $result = Captain::where('freelancer_id', $freelancer_id)->update($record);
        return ($result) ? true : false;
    }

    public static function sendFreelancerResponse($inputs) {
        $result = true;
        $frelancerId = CommonHelper::getFreelancerIdByUuid($inputs['freelancer_uuid']);
//        $onBoard = ['onboard_count' => !empty($inputs['onboard_count']) ? $inputs['onboard_count'] : 4];
        $onBoard = ['onboard_count' => !empty($inputs['onboard_count']) ? $inputs['onboard_count'] : null];
        if (!empty($onBoard['onboard_count'])) {
            $result = !empty($onBoard) ? Freelancer::where('id', $frelancerId)->update($onBoard) : true;
        }
        if ($result) {
            $freelancer = Freelancer::getFreelancerDetail('freelancer_uuid', $inputs['freelancer_uuid']);
            $response = FreelancerResponseHelper::freelancerProfileResponse($freelancer);
            DB::commit();
            return CommonHelper::jsonSuccessResponse(FreelancerMessageHelper::getMessageData('success', $inputs['lang'])['update_success'], $response);
        }

        DB::rollBack();
        return CommonHelper::jsonErrorResponse(FreelancerMessageHelper::getMessageData('error', $inputs['lang'])['update_error']);
    }

}

?>
