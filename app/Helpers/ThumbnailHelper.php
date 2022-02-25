<?php

namespace App\Helpers;

use Aws\Lambda\LambdaClient;

Class ThumbnailHelper {
    /*
      |--------------------------------------------------------------------------
      | ThumbnailHelper that contains media related methods for APIs
      |--------------------------------------------------------------------------
      |
      | This Helper controls all the methods that use media processes
      |
     */

    /**
     * Description of ThumbnailHelper
     *
     * @author ILSA Interactive
     */
    public static function processThumbnails($media_key = null, $media_category = null, $user_type = null) {

        if ($media_category == 'profile_image' && $user_type == 'freelancer') {

            return self::processImageThumbnails('uploads/profile_images/freelancers/' . $media_key);
        } elseif ($media_category == 'profile_image' && $user_type == 'customer') {
            return self::processImageThumbnails('uploads/profile_images/customers/' . $media_key);
        } elseif ($media_category == 'cover_image' && $user_type == 'freelancer') {
            return self::processImageThumbnails('uploads/cover_images/freelancers/' . $media_key);
        } elseif ($media_category == 'cover_image' && $user_type == 'customer') {
            return self::processImageThumbnails('uploads/cover_images/customers/' . $media_key);
        } elseif ($media_category == 'post_video' && $user_type == 'freelancer') {
            return self::processVideoThumbnails('uploads/posts/post_videos/' . $media_key);
        } elseif ($media_category == 'message_video') {
            return self::processVideoThumbnails('uploads/message_attachments/' . $media_key);
        }
        $response['success'] = false;
        $response['data']['errorMessage'] = 'Could not process image thumbnails';
        return $response;
    }

    public static function processImageThumbnails($image_key = null) {

        $client = LambdaClient::factory([
                    'version' => 'latest',
                    'region' => config('paths.s3_bucket_region'),
        ]);

        $result = $client->invoke([
            // The name your created Lamda function
            'InvocationType' => 'RequestResponse',
            'FunctionName' => 'resizeImages',
            'Payload' => json_encode(["s3_key" => $image_key])
//            'Payload' => json_encode(["s3_key" => "uploads/profile_images/customers/0010CD16-FFEB-490B-9FBE-38F3E3096413-1.jpeg"])
        ]);
        $response = json_decode($result->get('Payload')->getContents(), true);
        if (empty($response)) {
            return ['success' => true, 'data' => []];
        }
        return ['success' => false, 'data' => $response];
    }

    public static function processVideoThumbnails($video_key = null) {
        $client = LambdaClient::factory([
                    'version' => 'latest',
                    'region' => config('paths.s3_bucket_region'),
        ]);
        $result = $client->invoke([
            // The name your created Lamda function
            'InvocationType' => 'RequestResponse',
            'FunctionName' => 'makeThumbs',
            'Payload' => json_encode(["s3_key" => $video_key])
        ]);
        $response = json_decode($result->get('Payload')->getContents(), true);
        if (empty($response)) {
            return ['success' => true, 'data' => []];
        }
        return ['success' => false, 'data' => $response];
    }

}

?>
