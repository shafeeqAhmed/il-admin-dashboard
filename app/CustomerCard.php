<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CustomerCard extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_cards';

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
    protected $uuidFieldName = 'customer_card_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'card_id',
        'token',
        'card_name',
        'card_type',
        'last_digits',
        'expiry',
        'card_holder_name',
        'customer_checkout_id',
        'bin',
    ];

    public static function saveData($data) {
        $result = CustomerCard::create($data);
        return !empty($result) ? $result->toArray() : [];
    }

    public static function checkCardEntry($customerId, $cardId) {
        return CustomerCard::where('customer_id', $customerId)->where('card_id', $cardId)->where('is_archive', 0)->exists();
    }

    public static function getCustomerCard($customerId, $cardId) {
        return CustomerCard::where('customer_id', $customerId)->where('card_id', $cardId)->first()->id;
    }

    public static function getCustomerSingleCard($customerId, $cardId) {
        $result = CustomerCard::where('customer_id', $customerId)->where('customer_card_uuid', $cardId)->first();
        return !empty($result) ? $result->toArray() : [];
    }

    public static function getCustomerCards($customerId) {
        $result = CustomerCard::where('customer_id', $customerId)
                ->where('is_archive', 0)
                ->orderBy('created_at', 'desc')
                ->get();
        return !empty($result) ? $result->toArray() : [];
    }

    public static function updateData($col, $val, $data) {
        $result = true;
        if (CustomerCard::where($col, $val)->where('is_archive', '=', 0)->exists()) {
            $result = CustomerCard::where($col, $val)->where('is_archive', '=', 0)->update($data);
        }
        return ($result) ? true : false;
    }

}
