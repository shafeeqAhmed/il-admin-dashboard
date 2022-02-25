<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model {

    protected $table = 'boat_services';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'boat_services';
    public $timestamps = true;
    protected $fillable = [
        'qualification_uuid',
        'freelancer_id',
        'title',
        'description',
        'is_archive'
    ];

    protected function saveQualifications($data) {
        return Qualification::insert($data);
    }

    protected function deleteQualifications($column, $value) {
        return Qualification::where($column, '=', $value)->delete();
    }

}
