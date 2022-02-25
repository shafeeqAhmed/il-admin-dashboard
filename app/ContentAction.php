<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentAction extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'content_actions';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'content_action_uuid';
    public $timestamps = true;
    protected $fillable = [
        'content_action_uuid',
        'content_id',
        'content_type',
        'profile_uuid',
        'is_hidden',
        'is_archive'
    ];

    protected function saveHideContent($data) {
        $result = self::create($data);
        return !empty($result) ? $result->toArray() : [];
    }

    protected function pluckData($column, $value, $pluck_column = 'id') {
        $result = self::where($column, '=', $value)->pluck($pluck_column);
        return !empty($result) ? $result->toArray() : [];
    }

    protected function existenceCheck($data) {
        $result = self::where('profile_uuid', '=', $data['profile_uuid'])
                ->where('content_type', '=', $data['content_type'])
                ->where('content_uuid', '=', $data['content_uuid'])
                ->where('is_hidden', '=', $data['is_hidden'])
                ->first();
        return !empty($result) ? $result->toArray() : [];
    }

}
