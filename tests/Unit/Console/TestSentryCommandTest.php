<?php

namespace Tests\Unit\Console;

use App\Console\Commands\TestSentryCommand;
use Illuminate\Support\Facades\Log;
use Mockery;
use Sentry\ClientInterface;
use Sentry\HttpClient\HttpClientInterface;
use Sentry\HttpClient\Request as SentryRequest;
use Sentry\HttpClient\Response as SentryResponse;
use Sentry\Options;
use Sentry\State\HubInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

class TestSentryCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_fails_when_sentry_dsn_is_not_configured(): void
    {
        $options = new Options(['dsn' => null]);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('getOptions')->andReturn($options);

        $hub = Mockery::mock(HubInterface::class);
        $hub->shouldReceive('getClient')->andReturn($client);

        $this->app->instance(HubInterface::class, $hub);

        $this->artisan('sentry:connection-test')
            ->expectsOutput('Sentry DSN is not configured.')
            ->assertFailed();
    }

    public function test_fails_when_laravel_hub_has_no_client(): void
    {
        $hub = Mockery::mock(HubInterface::class);
        $hub->shouldReceive('getClient')->andReturn(null);

        $this->app->instance(HubInterface::class, $hub);

        $this->artisan('sentry:connection-test')
            ->expectsOutput('Sentry DSN is not configured.')
            ->assertFailed();
    }

    public function test_mask_dsn_redacts_credentials(): void
    {
        $command = new TestSentryCommand;
        $method = new \ReflectionMethod(TestSentryCommand::class, 'maskDsn');
        $method->setAccessible(true);

        $masked = $method->invoke($command, 'https://secret-key@sentry.example/42');

        $this->assertSame('https://***@sentry.example/42', $masked);
    }

    public function test_record_helpers_store_transport_and_sdk_messages(): void
    {
        $command = new TestSentryCommand;
        $command->recordTransportResult('Result: "success"');
        $command->recordSdkError('boom');

        $transport = new \ReflectionProperty(TestSentryCommand::class, 'transportResults');
        $transport->setAccessible(true);
        $errors = new \ReflectionProperty(TestSentryCommand::class, 'sdkErrors');
        $errors->setAccessible(true);

        $this->assertSame(['Result: "success"'], $transport->getValue($command));
        $this->assertSame(['boom'], $errors->getValue($command));
    }

    public function test_sdk_logger_records_results_errors_and_verbosity(): void
    {
        $command = Mockery::mock(TestSentryCommand::class)->makePartial();
        $command->shouldReceive('line')
            ->once()
            ->with('SDK(info): hello', null, OutputInterface::VERBOSITY_VERBOSE);
        $command->shouldReceive('line')
            ->once()
            ->with('SDK(error): Result: "success"', null, OutputInterface::VERBOSITY_NORMAL);
        $command->shouldReceive('recordTransportResult')->once()->with('Result: "success"');
        $command->shouldReceive('recordSdkError')->once()->with('Result: "success"');

        $method = new \ReflectionMethod(TestSentryCommand::class, 'makeSdkLogger');
        $method->setAccessible(true);
        $logger = $method->invoke($command);

        $logger->log('info', 'hello');
        $logger->log('error', 'Result: "success"');

        $this->assertTrue(true);
    }

    public function test_connection_test_sends_events_and_reports_success(): void
    {
        $this->bindLaravelHubWithDsn(httpSucceeds: true);

        Log::shouldReceive('channel')->with('sentry')->andReturnSelf();
        Log::shouldReceive('error')->once();

        $command = $this->makeRunnableCommand();
        $status = $command->run(new ArrayInput([]), new BufferedOutput);

        $this->assertSame(TestSentryCommand::SUCCESS, $status);
    }

    public function test_connection_test_fails_when_transport_reports_error(): void
    {
        $this->bindLaravelHubWithDsn(httpSucceeds: false, expectFlush: false);

        Log::shouldReceive('channel')->with('sentry')->andReturnSelf();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        $command = $this->makeRunnableCommand();
        $output = new BufferedOutput;
        $status = $command->run(new ArrayInput([]), $output);

        $this->assertSame(TestSentryCommand::FAILURE, $status);
        $fetched = $output->fetch();
        $this->assertTrue(
            str_contains($fetched, 'Failed to queue')
            || str_contains($fetched, 'No HTTP transport result')
            || str_contains($fetched, 'non-success result')
            || str_contains($fetched, 'SDK error')
            || str_contains($fetched, 'simulated transport failure')
            || str_contains($fetched, 'Could not create Sentry client')
        );
    }

    public function test_connection_test_with_structured_logs_option_swallows_log_channel_errors(): void
    {
        $this->bindLaravelHubWithDsn(httpSucceeds: true);

        $sentryChannel = Mockery::mock();
        $sentryChannel->shouldReceive('error')->once();

        $logsChannel = Mockery::mock();
        $logsChannel->shouldReceive('error')
            ->once()
            ->andThrow(new \RuntimeException('structured logs unavailable'));

        Log::shouldReceive('channel')->with('sentry')->andReturn($sentryChannel);
        Log::shouldReceive('channel')->with('sentry_logs')->andReturn($logsChannel);

        $command = $this->makeRunnableCommand();
        $status = $command->run(
            new ArrayInput(['--with-structured-logs' => true]),
            new BufferedOutput
        );

        $this->assertSame(TestSentryCommand::SUCCESS, $status);
    }

    public function test_connection_test_fails_when_laravel_sentry_log_channel_throws(): void
    {
        $this->bindLaravelHubWithDsn(httpSucceeds: true, expectFlush: false);

        $sentryChannel = Mockery::mock();
        $sentryChannel->shouldReceive('error')
            ->once()
            ->andThrow(new \RuntimeException('sentry channel down'));

        Log::shouldReceive('channel')->with('sentry')->andReturn($sentryChannel);

        $command = $this->makeRunnableCommand();
        $output = new BufferedOutput;
        $status = $command->run(new ArrayInput([]), $output);

        $this->assertSame(TestSentryCommand::FAILURE, $status);
        $this->assertStringContainsString('Laravel sentry log channel failed', $output->fetch());
    }

    private function bindLaravelHubWithDsn(bool $httpSucceeds = true, bool $expectFlush = true): void
    {
        $httpClient = new class($httpSucceeds) implements HttpClientInterface
        {
            public function __construct(private bool $succeeds) {}

            public function sendRequest(SentryRequest $request, Options $options): SentryResponse
            {
                if (! $this->succeeds) {
                    throw new \RuntimeException('simulated transport failure');
                }

                return new SentryResponse(200, [], '');
            }
        };

        $options = new Options([
            'dsn' => 'http://publickey@127.0.0.1:9/1',
            'environment' => 'testing',
            'release' => 'test-release',
            'http_client' => $httpClient,
        ]);

        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('getOptions')->andReturn($options);
        if ($expectFlush) {
            $client->shouldReceive('flush')->once()->with(10);
        }

        $hub = Mockery::mock(HubInterface::class);
        $hub->shouldReceive('getClient')->andReturn($client);

        $this->app->instance(HubInterface::class, $hub);
    }

    private function makeRunnableCommand(): TestSentryCommand
    {
        $command = new TestSentryCommand;
        $command->setLaravel($this->app);

        return $command;
    }
}
