<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchases extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchases';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    protected $uuidFieldName = 'purchases_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'freelancer_id',
        'purchases_short_id',
        'purchase_datetime',
        'account_title',
        'type',
        'purchased_by',
        'purchased_in_currency',
        'service_provider_currency',
        'conversion_rate',
        'appointment_id',
        'class_booking_id',
        'purchased_package_id',
        'subscription_id',
        'customer_card_id ',
        'boatek_fee',
        'transaction_charges',
        'service_amount',
        'total_amount',
        'discount',
        'discount_type',
        'total_amount_percentage',
        'tax',
        'boatek_fee_percenatge',
        'is_refund',
        'status',
        'is_archive',
    ];

    public function transaction() {
        return $this->hasOne('\App\PurchasesTransition', 'purchase_id', 'id');
    }

    public function appointment() {
        return $this->belongsTo('\App\Appointment', 'appointment_id', 'id');
    }

    public function customer() {
        return $this->belongsTo('\App\Customer', 'customer_id', 'id');
    }

    public function freelancer() {
        return $this->belongsTo('\App\Freelancer', 'freelancer_id', 'id');
    }

    public static function createPurchase($params) {
        $result = Purchases::create($params);
        return ($result) ? $result->toArray() : null;
    }

    public static function updatePurchase($col, $val, $data) {
        $result = Purchases::where($col, $val)->update($data);
        return ($result) ? true : false;
    }

    protected function getPurchasesList($user_type, $user_type_id, $type = 'all', $search_params = [], $limit = null, $offset = null) {
        $appointments = array();
        if (!empty($user_type)) {
            $appointments = self::with([
                        'appointment.AppointmentCustomer.user',
                        'appointment.AppointmentFreelancer.user',
                        'appointment.promo_code',
                        'transaction',
                        'freelancer',
                        'customer'
            ]);
            if ($user_type == 'freelancer') {
                $appointments->where('freelancer_id', '=', $user_type_id);
            } elseif ($user_type == 'customer') {
                $appointments->where('customer_id', '=', $user_type_id);
            }

            $appointments->orderBy('id', 'desc');

            if (isset($search_params['status']) && !empty($search_params['status'])) {
                $appointments->where('status', $search_params['status']);
            }
            if ($type == 'first') {
                $result = $appointments->first();
            } else {
                if (!empty($offset)) {
                    $appointments = $appointments->offset($offset);
                }
                if (!empty($limit)) {
                    $appointments = $appointments->limit($limit);
                }
                $result = $appointments->get();
            }
        }
        return !empty($result) ? $result->toArray() : [];
    }

    public static function getPurchaseDetail($col, $val) {
        $result = Purchases::where($col, $val)
                ->with('transaction')
                ->with('appointment.promo_code')
                ->with('customer.user')
                ->with('freelancer.user')
                ->first();
        return $result->toArray() ?? [];
    }
    public static function getPurchaseBalance($status,$column,$user_type_id) {
        return  Purchases::where('status',$status)->where($column,$user_type_id)->sum('total_amount');

    }


}
