<?php

namespace App;

use App\Helpers\UuidHelper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {

    use Notifiable;
    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = true;
    protected $uuidFieldName = 'user_uuid';
    protected $fillable = [
        'user_uuid',
        'freelancer_id',
        'profile_type',
        'first_name',
        'last_name',
        'email',
        'gender',
        'address',
        'address_comments',
        'phone_number',
        'country_code',
        'country_name',
        'password',
        'profile_image',
        'profile_card_image',
        'cover_video',
        'cover_video_thumb',
        'bio',
        'has_bank_detail',
        'cover_image',
        'type',
        'facebook_id',
        'google_id',
        'apple_id',
//        'onboard_count',
        'default_currency',
        'is_verified',
        'is_active',
        'is_login',
        'public_chat',
        'is_archive'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
//    protected $hidden = [
//        'password', 'remember_token',
//    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function SocialMedia() {
        return $this->hasMany('\App\SocialMedia', 'user_id', 'id');
    }

    public function userFreelancer() {
        return $this->hasOne('App\Freelancer', 'user_id', 'id');
    }

    public function freelancers() {
        return $this->hasMany('App\Freelancer', 'user_id', 'id')->where('is_archive', '=', 0);
    }

    public function userCustomer() {
        return $this->hasOne('App\Customer', 'user_id', 'id');
    }

    public function AppointmentClient() {

        return $this->hasMany('\App\Appointment', 'customer_id', 'id');
    }

    public static function getUserChild($userType, $userId) {
        if ($userType == 'freelancer') {
            return Freelancer::find($userId)->freelancer_uuid;
        } else {
            return Customer::find($userId)->customer_uuid;
        }
    }

    public static function getUserId($userType, $userId) {
        $freelancer = Freelancer::where('user_id', $userId)->first();

        if ($freelancer != null) {
            return $freelancer->freelancer_uuid;
        } else {

            return Customer::where('user_id', $userId)->first()->customer_uuid;
        }
    }

    public static function getUserChildren($userType, $userId) {
        if (!empty($userType) && $userType == 'freelancer') {
            return Freelancer::where('user_id', $userId)->first()->freelancer_uuid;
        } elseif (!empty($userType) && $userType == "customer") {
            return Customer::where('user_id', $userId)->first()->customer_uuid;
        } elseif (empty($userType)) {
            return User::where('id', $userId)->first()->user_uuid;
        }
    }

    public static function saveUser() {

        $user = User::create(['user_id' => UuidHelper::generateUniqueUUID("users", "user_uuid")]);

        return !empty($user) ? $user->toArray() : [];
    }

    public static function addUser($data) {
        $user = User::create($data);

        return !empty($user) ? $user->toArray() : [];
    }

    protected function checkUserExistByPhone($phn) {
        $resp = self::where('phone_number', $phn)->first();
        return $resp;
    }

    protected function getUserDetail($column, $value) {
        $result = User::where($column, '=', $value)
                ->with('SocialMedia')
                ->with('userFreelancer')
                ->withCount('freelancers')
                ->with('userCustomer')
                ->first();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function getUserDetailWithType($column, $value, $type = null) {
        $result = User::where($column, '=', $value)
                ->where('profile_type', '=', $type)
                ->with('SocialMedia')
                ->with('userFreelancer')
                ->withCount('freelancers')
                ->with('userCustomer')
                ->first();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function updateUser($column, $value, $data) {
        return User::where($column, '=', $value)->update($data);
    }

    protected function checkUser($col, $val) {
        $result = User::where($col, $val)->where('is_archive', '=', 0)->with('userCustomer')->first();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function pluckUserAttribute($column, $value, $pluck_column = 'id') {
        $result = User::where($column, '=', $value)->pluck($pluck_column);
        return !empty($result) ? $result->toArray() : [];
    }

    public function routeNotificationForSms(?Notification $notication = null) {
        return $this->phone_number;
    }

    public static function searchCustomersForChat($search_key = "", $ids = [], $limit = 100, $offset = 0) {
        $query = User::whereIn('id', $ids)
                ->where(function ($q) use ($search_key) {
            $q = $q->where(function ($sql) use ($search_key) {
                $sql = $sql->where('first_name', 'like', "%$search_key%");
            });
            $q = $q->ORWhere(function ($sql) use ($search_key) {
                $sql = $sql->where('last_name', 'like', "%$search_key%");
            });
        });
        $users = self::getSearch($query, $offset, $limit);
        return (!empty($users)) ? $users->toArray() : [];
    }

    public static function getSearch($query, $offset, $limit) {
        $query = $query->where('is_archive', '=', 0);
        $query = $query->with('userCustomer');

//        $query = $query->where('public_chat', '=', 1);
        $query = $query->limit($limit);
        $query = $query->offset($offset);
        $users = $query->get();

        return !empty($users) ? $users : [];
    }

}
