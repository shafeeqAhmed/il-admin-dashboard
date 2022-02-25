<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {

    use \BinaryCabin\LaravelUUID\Traits\HasUUID;

    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $uuidFieldName = 'subscription_uuid';
    public $timestamps = true;
    protected $fillable = [
        'subscription_uuid',
        'subscription_settings_id',
        'subscriber_id',
        'subscribed_id',
        'subscription_date',
        'subscription_end_date',
        'transaction_id',
        'card_registration_id',
        'price',
        'auto_renew',
        'is_archive'
    ];

    protected function createSubscription($data) {
        $result = Subscription::create($data);
        return !empty($result) ? $result->toArray() : [];
    }

    public function subscription_setting() {
        return $this->hasOne('\App\SubscriptionSetting', 'subscription_settings_uuid', 'subscription_settings_uuid');
    }

    public function customer() {
        return $this->hasOne('\App\Customer', 'customer_uuid', 'subscriber_uuid');
    }

    protected function checkSubscription($column, $value) {
        return Subscription::where($column, '=', $value)->where('is_archive', '=', 0)->first();
    }

    protected function checkSubscriber($subscriber_id, $subscribed_id) {
        $result = Subscription::where('subscriber_id', '=', $subscriber_id)->where('subscribed_id', '=', $subscribed_id)
            ->where('is_archive', '=', 0)
            ->first();
        return !empty($result) ? true : false;
    }

    protected function checkSubscriberPost($subscriber_uuid, $subscribed_uuid) {
        $result = Subscription::where('subscriber_uuid', '=', $subscriber_uuid)->where('subscribed_uuid', '=', $subscribed_uuid)
            ->where('is_archive', '=', 0)
            ->whereDate('subscription_end_date','>=', date('Y-m-d'))
            ->first();
        return !empty($result) ? true : false;
    }

    protected function getFreelancerFollowers($column, $value) {
        $result = self::with('GetFollower')->where($column, '=', $value)->where('is_archive', '=', 0)->get();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function getSubscribedIds($column, $value) {
        $result = self::where($column, '=', $value)->where('is_archive', '=', 0)->pluck("subscribed_uuid");
        return !empty($result) ? $result->toArray() : [];
    }

    protected function getSubscribers($column, $value, $limit = null, $offset = null) {
        $query = Subscription::where($column, '=', $value)->where('is_archive', '=', 0);
        $query = $query->with('customer');
        $query = $query->with('subscription_setting');
        if (!empty($offset)) {
            $query = $query->offset($offset);
        }
        if (!empty($limit)) {
            $query = $query->limit($limit);
        }
        $query = $query->orderBy('created_at', 'DESC');
        $result = $query->get();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function getSubscribersCount($column, $value) {
        return Subscription::where($column, '=', $value)->where('is_archive', 0)->count();
    }

    public static function getFavouriteProfileIds($column, $value, $pluck_field) {

        $result = Subscription::where($column, '=', $value)->where('is_archive', '=', 0)
            ->whereDate('subscription_end_date','>=', date('Y-m-d'))
            ->pluck($pluck_field);
        return !empty($result) ? $result->toArray() : [];
    }

    public static function getActiveSubscriptions() {
        $result = Subscription::where('is_archive', '=', 0)->where('auto_renew', 1)->with('subscription_setting')->get();
        return !empty($result) ? $result->toArray() : [];
    }

    public static function getActiveCancelledSubscriptions() {
        $result = Subscription::where('is_archive', '=', 0)->where('auto_renew', 0)->with('subscription_setting')->get();
        return !empty($result) ? $result->toArray() : [];
    }

    protected function cancelSubscription($col, $val, $data) {
        return Subscription::where($col, '=', $val)->where('is_archive', '=', 0)->update($data);
    }

}
