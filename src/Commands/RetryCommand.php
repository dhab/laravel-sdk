<?php

namespace DreamHack\SDK\Commands;

use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RetryCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'queue:retry {id* : The ID of the failed job or "all" to retry all jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry a failed queue job (DreamHack version)';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $extend = $this->choice('Do you want to extend timeoutAt timestamp with 2 hours?', ['Y', 'N'], 0);

        foreach ($this->getJobIds() as $id) {
            $job = $this->laravel['queue.failer']->find($id);

            if (is_null($job)) {
                $this->error("Unable to find failed job with ID [{$id}].");
            } else {
                $this->retryJob($job, $extend);

                $this->info("The failed job [{$id}] has been pushed back onto the queue!");

                $this->laravel['queue.failer']->forget($id);
            }
        }
    }

    /**
     * Get the job IDs to be retried.
     *
     * @return array
     */
    protected function getJobIds()
    {
        $ids = (array) $this->argument('id');

        if (count($ids) === 1 && $ids[0] === 'all') {
            $ids = Arr::pluck($this->laravel['queue.failer']->all(), 'id');
        }

        return $ids;
    }

    /**
     * Retry the queue job.
     *
     * @param  \stdClass  $job
     * @return void
     */
    protected function retryJob($job, $extend)
    {
        $this->laravel['queue']->connection($job->connection)->pushRaw(
            $this->resetAttemptsAndTimeoutAt($job->payload, $extend),
            $job->queue
        );
    }

    /**
     * Reset the payload attempts.
     *
     * Applicable to Redis jobs which store attempts in their payload.
     *
     * @param  string  $payload
     * @return string
     */
    protected function resetAttemptsAndTimeoutAt($payload, $extend)
    {
        $payload = json_decode($payload, true);

        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        if (isset($payload['timeoutAt'])) {
            $payload['timeoutAt'] = Carbon::now()->addHours(2)->timestamp;
        }

        return json_encode($payload);
    }
}
