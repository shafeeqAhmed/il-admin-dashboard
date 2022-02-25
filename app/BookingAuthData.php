<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingAuthData extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking_authorization_data';

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
    protected $uuidFieldName = 'booking_auth_uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fort_id',
        'merchant_reference',
        'amount',
    ];

    public static function saveData($data) {
        $result = BookingAuthData::create($data);
        return !empty($result) ? $result->toArray() : [];
    }

    public static function getBookingAuthData($col, $val) {
        $result = BookingAuthData::where($col, '=', $val)->where('is_archive', '=', 0)->first();
        return !empty($result) ? $result->toArray() : [];
    }

}
