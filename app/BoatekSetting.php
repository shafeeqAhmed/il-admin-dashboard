<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoatekSetting extends Model
{
    protected $guarded = ['id'];
    protected $table = 'boatek_settings';
    protected function getBoatekSetting($fetchColumn=null) {
        $query = self::where('is_archive',0);
        if(!is_null($fetchColumn)) {
             return $query->value($fetchColumn);
        }
        return $query->first();
    }
}
