<?php

namespace App\Console\Commands\EveryDay;

use App\Models\Alarms\Alarm;
use App\Models\Cron\Cron;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Alarms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alarm:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar las alarmas pendientes del día para mostrarlas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now()->toDateString();
        $ok = Alarm::where('showing_date', $now)->where('status_id', 2)->update(['status_id' => 1]);

        $cron = new Cron();
        $cron->cron = "Alarmas Diarias";
        $cron->command = $this->signature;
        $cron->created_at = Carbon::now()->toDateTimeString();
        $ok = $cron->save();

        return $ok;
    }
}
