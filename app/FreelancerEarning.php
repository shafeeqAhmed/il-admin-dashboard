<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FreelancerEarning extends Model {

    protected $table = 'freelancer_earnings';
    protected $uuidFieldName = 'freelancer_earning_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'freelancer_location_uuid',
        'freelancer_id',
        'earned_amount',
        'purchase_id',
        'amount_due_on',
        'currency',
        'freelancer_withdrawal_id',
        'is_blocked',
        'status',
    ];

    /*
     * All model relations goes down here
     *
     */

    protected function saveEarning($data) {
        $result = FreelancerEarning::create($data);
        return $result ? $result->toArray() : [];
    }

}
