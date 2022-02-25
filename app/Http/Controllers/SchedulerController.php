<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Helpers\ExceptionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SchedulerController extends Controller {
    /**
     * Description of SchedulerController
     *
     * @author ILSA Interactive
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function scheduler(Request $request) {
        Artisan::call('appointment:reminder');
//        Artisan::call('pending_booking:reminder');
        Artisan::call('change_status:reminder');
//        Artisan::call('paymentreq:cron');
//        
//        $schedule->command('appointment:reminder')->cron('* * * * *');
//        $schedule->command('pending_booking:reminder')->cron('* * * * *');
//        $schedule->command('change_status:reminder')->cron('* * * * *');
//        $schedule->command('paymentreq:cron')->cron('* * * * *');
        $response = ['message' => "process successfully executed"];
        return response()->json($response);
    }

}
