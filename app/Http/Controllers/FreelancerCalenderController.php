<?php

namespace App\Http\Controllers;

use App\Helpers\FreelancerCalenderHelper;
use App\Helpers\ExceptionHelper;
use Illuminate\Http\Request;

class FreelancerCalenderController extends Controller {

    /**
     * Description of FreelancerCalenderController
     *
     * @author ILSA Interactive
     */
    public function getFreelancerCalender(Request $request) {
        try {
            $inputs = $request->all();
            $inputs['lang'] = !empty($inputs['lang']) ? $inputs['lang'] : 'EN';
            return FreelancerCalenderHelper::getFreelancerCalender($inputs);
        } catch (\Illuminate\Database\QueryException $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        } catch (\Exception $ex) {
            return ExceptionHelper::returnAndSaveExceptions($ex, $request);
        }
    }

}
