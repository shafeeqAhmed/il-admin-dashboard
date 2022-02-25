<?php

namespace App\Http\Controllers;

use App\Captain;
use App\Helpers\CaptainHelper;
use App\Helpers\CategoryValidationHelper;
use App\Helpers\CommonHelper;
use App\Helpers\ExceptionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class CaptainController extends Controller {

    public function addCaptionProfile(Request $request) {
        DB::beginTransaction();
        try {
            return CaptainHelper::CaptainRequest($request->all());
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollback();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

}
