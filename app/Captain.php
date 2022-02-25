<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Captain extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'captain_profile';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'captain_uuid';
    public $timestamps = true;
    protected $fillable = [
        'captain_uuid',
        'freelancer_id',
        'captain_name',
        'captain_image',
        'created_at',
        'updated_at'
    ];


     public static function createCaptain($params){
         $results = Captain::insert($params);
         return $results;
     }

}
