<?php

namespace App\Helpers;

Class UserMessageHelper {
    /*
      |--------------------------------------------------------------------------
      | FreelancerMessageHelper that contains all the Freelancer message methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use Freelancer processes
      |
     */

    public static function getMessageData($type = '', $language = 'EN') {
        if (strtolower($language) == 'ar' && $type == 'error') {
            return self::returnArabicErrorMessage();
        } elseif (strtolower($language) == 'en' && $type == 'error') {
            return self::returnEnglishErrorMessage();
        } elseif (strtolower($language) == 'ar' && $type == 'success') {
            return self::returnArabicSuccessMessage();
        } elseif (strtolower($language) == 'en' && $type == 'success') {
            return self::returnEnglishSuccessMessage();
        }
    }

    public static function returnEnglishSuccessMessage() {
        return [
            'successful_request' => 'Request successful!',
            'update_success' => 'Profile updated successfully!',
            'change_password_success' => 'Password successfully updated'
        ];
    }

    public static function returnArabicSuccessMessage() {
        return [
            'successful_request' => 'طلب ناجح!',
            'update_success' => 'تم تحديث الملف الشخصي بنجاح!',
            'change_password_success' => 'تم تحديث كلمة المرور بنجاح'
        ];
    }

    public static function returnEnglishErrorMessage() {
        return [
            'general_error' => 'Sorry, something went wrong. We are working on getting this fixed as soon as we can',
            'update_settings_error' => "Sorry, settings could not be updated",
            'update_error' => 'Profile could not be updated',
            'change_password_error' => 'Change password failed',
            'same_password_error' => 'New password and old password can not be same',
            'save_category_error' => 'Freelancer category could not be saved',
            'old_password_error' => "Old password is incorrect",
            'invalid_uuid' => "User does not exists",
        ];
    }

    public static function returnArabicErrorMessage() {
        return [
            'general_error' => 'وجه الفتاة! حدث خطأ ما. أعد المحاولة من فضلك',
            'update_error' => 'لا يمكن تحديث الملف الشخصي',
            'change_password_error' => 'فشل تغيير كلمة المرور',
            'save_category_error' => 'لا يمكن حفظ فئة المستقل',
            'invalid_data_error' => 'البيانات غير صالحة المقدمة',
            'old_password_error' => "Old password is incorrect",
            'same_password_error' => 'New password and old password can not be same',
            'invalid_uuid' => "المترجم المستقل غير موجود",
        ];
    }

}

?>
