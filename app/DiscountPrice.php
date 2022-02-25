<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountPrice extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'boat_discounts';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'discount_uuid';
    public $timestamps = true;
    protected $fillable = [
        'discount_uuid',
        'discount_after',
        'percentage',
        'freelancer_id',
        'created_at',
        'updated_at'
    ];


     public static function createDiscount($params){
        $results = DiscountPrice::insert($params);
        return $results;
    }

    public static function updateData($col, $val, $data) {
        $results = DiscountPrice::where($col, $val)->where('is_archive', 0)->update($data);
        return $results ? true : false;
    }

}
