<?php

namespace App\Console;

//use App\Console\Commands\AppointmentReminderEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ResubscribeUser::class,
        Commands\RemoveExpiredSubscription::class,
        Commands\AppointmentReminderEmail::class,
        Commands\PendingBookingsReminder::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // $schedule->command('inspire')
        //          ->hourly();
//        $schedule->command('appointment:reminder')->hourly();
//        $schedule->command('pending_booking:reminder')->hourly();
        $schedule->command('appointment:reminder')->cron('* * * * *');
        $schedule->command('pending_booking:reminder')->cron('* * * * *');
        $schedule->command('change_status:reminder')->cron('* * * * *');
        $schedule->command('paymentreq:cron')->cron('* * * * *');
        $schedule->command('subscription:remove')->daily();
//        $schedule->command('pending_booking:reminder')
//                ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
