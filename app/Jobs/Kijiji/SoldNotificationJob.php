<?php

declare(strict_types=1);

namespace App\Jobs\Kijiji;

use App\Mail\ListingSoldMail;
use App\Models\KijijiListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends listing sold notification email.
 */
class SoldNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 30;

    public function __construct(
        public KijijiListing $listing
    ) {
        $this->onQueue('kijiji');
    }

    public function handle(): void
    {
        try {
            $email = config('mail.kijiji_notification_email', config('mail.from.address'));

            Mail::to($email)->send(new ListingSoldMail($this->listing));
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('SoldNotificationJob failed', [
                'listing_id' => $this->listing->id,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
