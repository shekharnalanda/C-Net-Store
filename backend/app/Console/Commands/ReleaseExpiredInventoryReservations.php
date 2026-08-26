<?php

namespace App\Console\Commands;

use App\Models\InventoryReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseExpiredInventoryReservations extends Command
{
    protected $signature = 'store:release-expired-reservations {--chunk=100}';
    protected $description = 'Return stock held by expired unpaid inventory reservations';

    public function handle(): int
    {
        $released = 0;
        $chunk = max(10, min(1000, (int) $this->option('chunk')));
        InventoryReservation::query()->where('status', 'reserved')->where('expires_at', '<=', now())->orderBy('id')
            ->chunkById($chunk, function ($reservations) use (&$released): void {
                foreach ($reservations as $reservation) {
                    DB::transaction(function () use ($reservation, &$released): void {
                        $locked = InventoryReservation::query()->lockForUpdate()->find($reservation->id);
                        if (! $locked || $locked->status !== 'reserved' || $locked->expires_at->isFuture()) return;
                        $inventory = $locked->inventory()->lockForUpdate()->first();
                        if ($inventory) $inventory->decrement('reserved_quantity', $locked->quantity);
                        $locked->update(['status' => 'released', 'released_at' => now()]);
                        $released++;
                    });
                }
            });
        $this->info("Released {$released} expired reservation(s).");
        return self::SUCCESS;
    }
}
