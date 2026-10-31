<?php

use App\Jobs\DeliverContentWebhook;
use App\Services\ContentWebhooks\ChangeRecorder;
use App\Services\ContentWebhooks\DeliveryService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('content-webhooks:dispatch', function () {
    if (! config('content_webhooks.enabled')) {
        $this->info('Content webhook delivery is disabled.');

        return;
    }
    $id = app(DeliveryService::class)->trigger('scheduled');
    $this->info('Queued delivery cycle '.$id);
});

Schedule::command('content-webhooks:dispatch')
    ->dailyAt('00:00')->timezone('Asia/Tehran')->withoutOverlapping();

// Recovery does not create new packs or publish new changes. It only projects
// committed audit records and wakes an already authorized delivery cycle.
Schedule::call(function () {
    if (! Schema::hasTable('content_delivery_state')) {
        return;
    }
    app(ChangeRecorder::class)->reconcile();
    $cycle = DB::table('content_delivery_state')->where('id', 1)->value('active_cycle');
    if ($cycle && config('content_webhooks.enabled')) {
        DeliverContentWebhook::dispatch($cycle);
    }
})->name('recover-content-webhooks')->everyMinute()->withoutOverlapping();
