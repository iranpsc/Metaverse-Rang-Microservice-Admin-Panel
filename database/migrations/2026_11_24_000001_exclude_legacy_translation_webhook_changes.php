<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $state = DB::table('content_delivery_state')->where('id', 1)->lockForUpdate()->first();
            DB::table('content_delivery_state')->where('id', 2)->lockForUpdate()->first();

            $batches = DB::table('content_webhook_batches')->whereIn('status', ['pending', 'failed']);
            $legacy = DB::table('content_changes')->where('data->feature', 'translations')
                ->where('data->event', '!=', 'exported');
            $affected = (clone $legacy)->whereIn('batch_id', (clone $batches)->select('id'))
                ->distinct()->pluck('batch_id');

            (clone $legacy)->where(fn ($query) => $query->whereNull('batch_id')->orWhereIn('batch_id', $affected))->delete();
            if ($affected->isEmpty()) {
                return;
            }

            // Preserve the frozen payload and attempt history. Republish surviving
            // changes with a new identity because the receiver may have seen the old one.
            $batches->whereIn('id', $affected)->update(['status' => 'superseded', 'updated_at' => now('UTC')]);
            DB::table('content_changes')->whereIn('batch_id', $affected)->update(['batch_id' => null]);
            $nextPatch = $state->next_patch;
            foreach (DB::table('content_webhook_batches')->whereIn('id', $affected)->pluck('version') as $version) {
                $nextPatch = max($nextPatch, (int) substr($version, strlen('V1.1.')) + 1);
            }
            DB::table('content_delivery_state')->where('id', 1)->update(['next_patch' => $nextPatch]);
        });
    }

    public function down(): void
    {
        // Retired events must not be made eligible for delivery again.
    }
};
