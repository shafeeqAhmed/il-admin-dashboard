<?php

namespace App\Http\Controllers;

use App\Helpers\FreelancerProfileHelper;
use Illuminate\Http\Request;
use App\Helpers\ExceptionHelper;
use App\Helpers\FreelancerHelper;
use DB;

class FreelancerProfileController extends Controller {

    public function changePassword(Request $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return FreelancerHelper::changePassword($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getSubscriptionSettings(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return FreelancerProfileHelper::getSubscriptionSettings($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function getFreelancerProfile(Request $request) {
       try {
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return FreelancerProfileHelper::getFreelancerProfile($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

    public function deleteMedia(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return FreelancerProfileHelper::deleteMedia($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }
    public function dummyMail(Request $request) {
        try {

            $mail = \App\Helpers\EmailSendingHelper::sendCodeEmail([
                'message'=>$request->has('message') ? $request->message : 'some dummy message test',
                'subject'=>$request->has('subject') ? $request->subject : 'Test',
                'email'=>$request->has('email') ? $request->email : 'meer.aali@ilsainteractive.com',
                'template'=>'emails.test_email'
            ]);
            dd("mail submitted successfully!");
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }

    }

}
