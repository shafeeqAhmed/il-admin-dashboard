<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Helpers\FreelancerHelper;
use Illuminate\Http\Request;
use App\Helpers\UserHelper;
use DB;
class UserController extends Controller
{
    public function updateUser(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return UserHelper::updateUser($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }
}
