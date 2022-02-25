<?php

namespace App\Http\Controllers;

use App\Helpers\HomeScreenHelper;
use App\Helpers\ExceptionHelper;
use Illuminate\Http\Request;
use DB;

class HomeController extends Controller {

    /**
     * Description of HomeController
     *
     * @author ILSA Interactive
     */
    public function customizeHomeScreen(Request $request) {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return HomeScreenHelper::customizeHomeScreen($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

}
