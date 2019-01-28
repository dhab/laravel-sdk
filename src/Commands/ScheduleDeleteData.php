<?php

namespace DreamHack\SDK\Commands;

use DreamHack\SDK\Traits\BaseUserData;

use Carbon\Carbon;
use Illuminate\Console\Command;

class ScheduleDeleteData extends Command
{
    use BaseUserData;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'userdata:schedule_delete {--test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete userdata on a schedule, run by cron normally';

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
     * Turns config-array to SQL for comparing dates
     *
     * @param array Table => Interval (goes straight to SQL)
     * @return string
     */
    private function transformWhen($when)
    {
        foreach ($when as $key => $interval) {
            $ret[] = "{$key} < DATE_SUB(NOW(), INTERVAL {$interval})";
        }
        return join(' AND ', $ret);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dummyUser = new \StdClass;
        $dummyUser->id = '';
        $dummyUser->email = '';

        $queries = [];
        foreach (config('userdata.schedule') as $table => $data) {
            $query = sprintf("DELETE FROM %s WHERE %s AND %s",
                $table,
                $this->transformWhere($data['where'], $dummyUser),
                $this->transformWhen($data['when']),
            );
            $queries[] = [$table => $query];
        }

        $test = $this->option('test');
        $ret = $this->runDeletes($queries, $test);
        if ($test) {
            foreach ($ret as $table => $rows) {
                $this->info("{$table}: Would delete {$rows} row(s)");
            }
        }
    }
}
