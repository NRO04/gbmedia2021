<?php

namespace App\Console\Commands\EveryHalfHour;

use App\Models\Cron\Cron;
use App\Models\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseInactivatedUserTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'close-inactivate-users:tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cerrar los trabajos de Novedad Personal despues de 2 dias';

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
        $now = Carbon::now()->toDateTimeString();

        $tasks = Task::where('title', 'LIKE', 'Novedad Personal')->where('status', 0)->where('should_finish', '<=', $now)->update(
            [
                'status' => 1,
                'terminated_by' => 549,
            ]
        );

        if($tasks > 0)
        {
            $cron = new Cron();
            $cron->cron = "Cerrar trabajos Novedad Personal (Cerrados: $tasks)";
            $cron->command = $this->signature;
            $cron->created_at = Carbon::now()->toDateTimeString();
            $ok = $cron->save();
        }

        return true;
    }
}
