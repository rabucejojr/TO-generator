<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TravelOrder;

class SyncTravelOrderFundInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'travel-orders:sync-expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync fund_source and fund_details from main columns into expenses JSON for all travel orders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = 0;

        $this->info('Starting sync of TravelOrder fund_source and fund_details into expenses JSON...');

        TravelOrder::chunk(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                $expenses = $order->expenses ?? [];

                // Merge fund info into expenses JSON
                $order->expenses = array_merge($expenses, [
                    'fund_source'  => $order->fund_source,
                    'fund_details' => $order->fund_details,
                ]);

                $order->saveQuietly(); // prevents triggering events/logging

                $count++;
                $this->output->write('.');
            }
        });

        $this->newLine(2);
        $this->info("✅ Successfully synced {$count} travel orders.");

        return Command::SUCCESS;
    }
}
