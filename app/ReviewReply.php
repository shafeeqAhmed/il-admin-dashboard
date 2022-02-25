<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'review_replies';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'reply_uuid';
    public $timestamps = true;
    protected $fillable = [
        'reply_uuid',
        'review_id',
        'user_id',
        'reply',
        'is_archive'
    ];

    public function user() {
        return $this->hasOne('\App\User', 'id', 'user_id');
    }

    public function customer() {
        return $this->hasOne('\App\Customer', 'customer_uuid', 'profile_uuid');
    }

    public function freelancer() {
        return $this->hasOne('\App\Freelancer', 'freelancer_uuid', 'profile_uuid');
    }

    protected function saveReply($data) {
        $result = ReviewReply::create($data);
        return ($result) ? $result : [];
    }

    protected function getSingleReply($column, $value) {
        $result = ReviewReply::where($column, '=', $value)
                ->with('user.userCustomer')
                ->with('user.userFreelancer')
//                ->with('freelancer')
                ->first();
        return !empty($result) ? $result->toArray() : [];
    }

}
