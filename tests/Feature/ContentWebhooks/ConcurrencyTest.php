<?php

namespace Tests\Feature\ContentWebhooks;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    public function test_killed_worker_recovers_frozen_pack_after_receiver_already_accepted(): void
    {
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            $this->markTestSkipped('Requires local Node receiver.');
        }
        $db = sys_get_temp_dir().'/webhook-concurrency-'.bin2hex(random_bytes(8)).'.sqlite';
        touch($db);
        $make = fn ($command) => new Process([PHP_BINARY, base_path('tests/Support/webhook-process.php'), $command, $db], base_path(), null, null, 30);
        $worker = null;
        try {
            $make('init')->mustRun();
            $make('trigger')->mustRun();
            $make('deliver')->mustRun();
            $state = json_decode($make('state')->mustRun()->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $original = $state['batches'][0];
            $worker = $make('crash-deliver');
            $worker->start();
            $accepted = false;
            $deadline = microtime(true) + 10;
            do {
                $accepted = in_array($original['id'], Http::get('http://127.0.0.1:4900/__test/requests')->json('accepted'));
                if (! $accepted) {
                    usleep(100000);
                }
            } while (! $accepted && microtime(true) < $deadline);
            $this->assertTrue($accepted, 'Receiver must accept before the worker is killed');
            $worker->stop(0);
            $state = json_decode($make('state')->mustRun()->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $this->assertEquals(1, $state['next_patch']);
            $this->assertSame('pending', $state['batches'][0]['status']);
            $this->assertSame($original['payload'], $state['batches'][0]['payload']);
            $make('deliver')->mustRun();
            $state = json_decode($make('state')->mustRun()->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            $this->assertEquals(2, $state['next_patch']);
            $this->assertSame('delivered', $state['batches'][0]['status']);
            $requests = collect(Http::get('http://127.0.0.1:4900/__test/requests')->json('requests'))->where('batch_id', $original['id'])->values();
            $this->assertCount(2, $requests);
            $this->assertTrue($requests[1]['duplicate']);
            $this->assertSame($requests[0]['raw'], $requests[1]['raw']);
        } finally {
            if ($worker?->isRunning()) {
                $worker->stop();
            }
            foreach ([$db, $db.'-wal', $db.'-shm'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    public function test_simultaneous_triggers_and_workers_create_and_deliver_only_one_pack(): void
    {
        if (! getenv('CONTENT_WEBHOOK_NODE_RECEIVER')) {
            $this->markTestSkipped('Requires the local Node receiver.');
        }
        $db = sys_get_temp_dir().'/webhook-concurrency-'.bin2hex(random_bytes(8)).'.sqlite';
        touch($db);
        $processes = [];
        $make = fn ($command, $barrier = 0) => new Process([PHP_BINARY, base_path('tests/Support/webhook-process.php'), $command, $db, (string) $barrier], base_path(), null, null, 30);
        try {
            $make('init')->mustRun();
            $barrier = microtime(true) + 2;
            $a = $make('trigger', $barrier);
            $b = $make('trigger', $barrier);
            $processes = [$a, $b];
            $a->start();
            $b->start();
            $a->wait();
            $b->wait();
            $this->assertSame(0, $a->getExitCode(), $a->getErrorOutput());
            $this->assertSame(0, $b->getExitCode(), $b->getErrorOutput());
            $this->assertJson($a->getOutput(), $a->getOutput().$a->getErrorOutput());
            $this->assertJson($b->getOutput(), $b->getOutput().$b->getErrorOutput());
            $this->assertSame(json_decode($a->getOutput(), true)['cycle'], json_decode($b->getOutput(), true)['cycle']);
            $make('deliver')->mustRun(); // Persist the immutable pack.
            $barrier = microtime(true) + 2;
            $a = $make('deliver', $barrier);
            $b = $make('deliver', $barrier);
            $processes = [$a, $b];
            $a->start();
            $b->start();
            $a->wait();
            $b->wait();
            $this->assertSame(0, $a->getExitCode(), $a->getErrorOutput());
            $this->assertSame(0, $b->getExitCode(), $b->getErrorOutput());
            $state = json_decode($make('state')->mustRun()->getOutput(), true);
            $this->assertCount(1, $state['batches']);
            $this->assertSame(1, $state['cycles']);
            $this->assertEquals(2, $state['next_patch']);
            $batch = $state['batches'][0];
            $this->assertSame('delivered', $batch['status']);
            $requests = collect(Http::get('http://127.0.0.1:4900/__test/requests')->json('requests'))->where('batch_id', $batch['id']);
            $this->assertCount(1, $requests);
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ([$db, $db.'-wal', $db.'-shm'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
