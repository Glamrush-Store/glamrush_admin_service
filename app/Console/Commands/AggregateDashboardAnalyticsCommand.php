<?php

namespace App\Console\Commands;

use App\Domain\Dashboard\Actions\BuildDashboardAnalyticsSnapshotAction;
use Illuminate\Console\Command;

class AggregateDashboardAnalyticsCommand extends Command
{
    protected $signature = 'dashboard:aggregate-analytics {--period=* : Limit aggregation to one or more periods}';

    protected $description = 'Aggregate dashboard analytics snapshots for configured admin dashboard periods.';

    public function handle(BuildDashboardAnalyticsSnapshotAction $buildSnapshot): int
    {
        $periods = $this->option('period') ?: config('dashboard.aggregation.periods', ['week', 'month', 'quarter', 'year']);

        foreach ($periods as $period) {
            $snapshot = $buildSnapshot->run((string) $period);
            $this->info("Aggregated dashboard analytics for {$snapshot->period} at {$snapshot->aggregated_at}.");
        }

        return self::SUCCESS;
    }
}
