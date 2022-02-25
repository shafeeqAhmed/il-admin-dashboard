<?php

namespace App\Helpers;

use App\Appointment;
use App\Purchases;

Class PurchaseHelper {
    
    public static function getFreelancerBalance($freelancer_uuid,$status) {
        $amount = 0;
        $freelancer_id = CommonHelper::getFreelancerIdByUuid($freelancer_uuid);
        if($freelancer_id) {

            if($status == 'pending') {
                $amount = Appointment::getAppointmentBalance($status,'freelancer_id',$freelancer_id);

            } elseif($status == 'succeeded') {
                $amount = Purchases::getPurchaseBalance($status,'freelancer_id',$freelancer_id);

            }
        }
        return $amount;
    }


}
