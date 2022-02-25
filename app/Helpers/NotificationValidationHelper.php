<?php

namespace App\Helpers;

Class NotificationValidationHelper {
    /*
      |--------------------------------------------------------------------------
      | NotificationValidationHelper_1 that contains all the notification Validation methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use notification processes
      |
     */

    public static function getNotificationRules() {
        $validate['rules'] = [
            'profile_uuid' => 'required',
            'logged_in_uuid' => 'required',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function updateNotificationStatusRules() {
        $validate['rules'] = [
            'profile_uuid' => 'required',
            'logged_in_uuid' => 'required',
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }
    public static function updateBoatNotificationSettingsRules() {
        $validate['rules'] = [
            'freelancer_uuid' => 'required',
            'logged_in_uuid' => 'required',
            'status' => 'required'
        ];
        $validate['message_en'] = self::englishMessages();
        $validate['message_ar'] = self::arabicMessages();
        return $validate;
    }

    public static function englishMessages() {
        return [
            'profile_uuid.required' => 'profile uuid is missing',
            'logged_in_uuid.required' => 'login uuid is missing',
            'freelancer_uuid.required' => 'Freelancer uuid is missing',
            'status.required' => 'notification status is missing',
            'invalid_data' => 'Invalid data provided',
        ];
    }

    public static function arabicMessages() {
        return [
            'profile_uuid.required' => 'الملف الشخصي uuid مفقود',
            'logged_in_uuid.required' => 'تسجيل الدخول uuid مفقود',
            'status.required' => 'حالة الإخطار مفقودة',
            'invalid_data' => 'تم تقديم بيانات غير صالحة',

        ];
    }

    public static $add_notification_uuid_rules = array(
        'notification_uuid' => 'required|unique:notifications',
    );

}

?>
