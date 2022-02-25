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

Class BoatHelper {

    public static function mapBoatResponse($boats,$user){

        return self::mapBoat($boats);


//        return [
//            'user'=>self::mapUser($user),
//            'boats'=>self::mapBoat($boats)
//        ];
    }

    public static function mapUser($user){
        return [
            'user_uuid'=>$user->user_uuid,
            'user_name'=>$user->first_name.' '.$user->last_name,
            'user_profile_image'=>$user->profile_image,
        ];
    }

    public static function mapBoat($boats){

        $records = [];
        foreach ($boats as $boat){

           $records[]= self::mapSingleBoat($boat);
        }

        return $records;
    }


    public static function mapSingleBoat($boat){
//dd($boat);
//        if (!empty($category['image'])) {
//            $response[$key]['image'] = !empty($category['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['freelancer_category_image'] . $category['image'] : null;
//        } elseif (!empty($category['sub_category'])) {
//            $response[$key]['image'] = !empty($category['sub_category']['image']) ? config('paths.s3_cdn_base_url') . CommonHelper::$s3_image_paths['category_image'] . $category['sub_category']['image'] : null;
//        }
        return  [
            'freelancer_uuid'=>$boat['freelancer_uuid'],
            'username'=>$boat['user']['first_name'].' '.$boat['user']['last_name'],
            'boat_name'=>$boat['first_name'].' '.$boat['last_name'],
            'freelancer_categories'=>FreelancerResponseHelper::freelancerCategoriesResponse($boat['freelancer_categories']),
            'profile_images'=> FreelancerResponseHelper::freelancerProfileImagesResponse($boat['profile_image']),
            'cover_images'=>FreelancerResponseHelper::freelancerCoverImagesResponse($boat['cover_image']),
            'onboard_count'=>$boat['onboard_count'],
            'freelancer_profile_image'=> FreelancerResponseHelper::freelancerCoverImagesResponse( $boat['profile_image']),
            'locations'=>LoginHelper::processFreelancerLocationsResponse((!empty($boat['locations']) ? $boat['locations'] : [])),
            'per_hour_price'=>self::BoatPriceObject($boat),
        ];

    }

    public static function BoatPriceObject($boat){

        return [
            'price'=>$boat['price'],
            'discount_after'=>self::boatDiscountAfter($boat)
        ];
    }

    public static function boatDiscountAfter($discountsAfter){
        $response = [];
        if(!empty($discountsAfter['discount'])){
            foreach ($discountsAfter['discount'] as $after){
                $response[] = [
                    'hours'=>$after['discount_after'],
                    'percentage'=>$after['percentage']
                ];
            }
        }
        return $response;
    }

}

?>
