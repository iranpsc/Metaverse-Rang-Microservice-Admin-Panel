<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\AbstractLogger;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\Laravel\Version;
use Sentry\Severity;
use Sentry\State\Hub;
use Sentry\State\HubInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class TestSentryCommand extends Command
{
    protected $signature = 'sentry:connection-test
                            {--with-structured-logs : Also try Sentry structured logs (often unsupported on self-hosted)}';

    protected $description = 'Verify Sentry connectivity by sending Issues and reporting HTTP delivery status';

    /** @var list<string> */
    private array $sdkErrors = [];

    /** @var list<string> */
    private array $transportResults = [];

    public function handle(HubInterface $laravelHub): int
    {
        if (! extension_loaded('curl')) {
            $this->error('PHP cURL extension (ext-curl) is required to talk to Sentry.');

            return self::FAILURE;
        }

        $laravelClient = $laravelHub->getClient();
        $dsnObject = $laravelClient?->getOptions()->getDsn();

        if ($dsnObject === null) {
            $this->error('Sentry DSN is not configured.');
            $this->line('Set SENTRY_LARAVEL_DSN in your .env (or docker-compose environment).');

            return self::FAILURE;
        }

        $dsn = (string) $dsnObject;

        $this->info('DSN: '.$this->maskDsn($dsn));
        $this->info('Environment: '.($laravelClient->getOptions()->getEnvironment() ?? app()->environment()));
        $this->warn('Important: look under Issues (Errors), not Logs. Self-hosted Sentry often has no Logs UI.');
        $this->line('UI: https://'.$dsnObject->getHost().'/');
        $this->newLine();

        $options = [
            'dsn' => $dsn,
            'environment' => $laravelClient->getOptions()->getEnvironment() ?? app()->environment(),
            'release' => $laravelClient->getOptions()->getRelease(),
            'sample_rate' => 1.0,
            'traces_sample_rate' => null,
            'enable_logs' => (bool) config('sentry.enable_logs', false),
            'before_send' => static function (Event $event): ?Event {
                return $event;
            },
            'http_client' => $laravelClient->getOptions()->getHttpClient(),
            'http_proxy' => $laravelClient->getOptions()->getHttpProxy(),
            'http_timeout' => $laravelClient->getOptions()->getHttpTimeout(),
            'http_connect_timeout' => $laravelClient->getOptions()->getHttpConnectTimeout(),
            'http_ssl_verify_peer' => $laravelClient->getOptions()->getHttpSslVerifyPeer(),
        ];

        try {
            $builder = ClientBuilder::create($options);
        } catch (Throwable $e) {
            $this->error('Could not create Sentry client: '.$e->getMessage());

            return self::FAILURE;
        }

        $builder->setSdkIdentifier(Version::SDK_IDENTIFIER);
        $builder->setSdkVersion(Version::SDK_VERSION);
        $builder->setLogger($this->makeSdkLogger());

        $hub = new Hub($builder->getClient());
        $eventIds = [];

        $this->info('1/3 Sending error message...');
        $messageId = $hub->captureMessage(
            'Sentry connection test message from '.config('app.name'),
            Severity::error(),
        );
        if ($messageId === null) {
            $this->error('Failed to queue error message.');

            return self::FAILURE;
        }
        $eventIds[] = (string) $messageId;
        $this->line("   Event ID: {$messageId}");

        $this->info('2/3 Sending exception...');
        try {
            throw new Exception('Sentry connection test exception from '.config('app.name'));
        } catch (Throwable $exception) {
            $exceptionId = $hub->captureException($exception);
        }
        if (! isset($exceptionId) || $exceptionId === null) {
            $this->error('Failed to queue exception.');

            return self::FAILURE;
        }
        $eventIds[] = (string) $exceptionId;
        $this->line("   Event ID: {$exceptionId}");

        $this->info('3/3 Sending via Laravel sentry log channel...');
        try {
            Log::channel('sentry')->error('Sentry connection test log from '.config('app.name'), [
                'source' => 'sentry:connection-test',
            ]);
            $this->line('   Queued on Laravel sentry channel (flushed with app client below).');
        } catch (Throwable $e) {
            $this->error('Laravel sentry log channel failed: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('with-structured-logs')) {
            $this->warn('Trying structured logs (needs Sentry Logs feature flags on the server)...');
            try {
                Log::channel('sentry_logs')->error('Sentry structured log test from {app}', [
                    'app' => config('app.name'),
                    'source' => 'sentry:connection-test',
                ]);
            } catch (Throwable $e) {
                $this->error('Structured log failed: '.$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Flushing transport...');
        $hub->getClient()?->flush(10);
        $laravelClient->flush(10);

        $this->newLine();
        if ($this->transportResults === []) {
            $this->error('No HTTP transport result received from Sentry.');
            foreach ($this->sdkErrors as $error) {
                $this->line(' - '.$error);
            }

            return self::FAILURE;
        }

        $allOk = true;
        foreach ($this->transportResults as $result) {
            $this->line('SDK: '.$result);
            if (! str_contains($result, 'Result: "success"') && ! str_contains($result, 'status: 200')) {
                $allOk = false;
            }
        }

        foreach ($this->sdkErrors as $error) {
            $this->error('SDK error: '.$error);
            $allOk = false;
        }

        $this->newLine();
        if (! $allOk) {
            $this->error('Sentry accepted the request with a non-success result. Check DSN/project permissions.');

            return self::FAILURE;
        }

        $this->info('Sentry server accepted the events (HTTP 200).');
        $this->line('In the Sentry UI open Issues (not Logs), set environment filter to "'.($laravelClient->getOptions()->getEnvironment() ?? app()->environment()).'", then search:');
        foreach ($eventIds as $id) {
            $this->line(' - '.$id);
        }

        return self::SUCCESS;
    }

    private function makeSdkLogger(): AbstractLogger
    {
        return new class($this) extends AbstractLogger
        {
            public function __construct(private TestSentryCommand $command) {}

            public function log($level, $message, array $context = []): void
            {
                $verbosity = in_array($level, ['debug', 'info', 'notice'], true)
                    ? OutputInterface::VERBOSITY_VERBOSE
                    : OutputInterface::VERBOSITY_NORMAL;

                $this->command->line("SDK({$level}): {$message}", null, $verbosity);

                if (str_contains($message, 'Result:')) {
                    $this->command->recordTransportResult($message);
                }

                if (in_array($level, ['error', 'critical'], true)) {
                    $this->command->recordSdkError($message);
                }
            }
        };
    }

    public function recordTransportResult(string $message): void
    {
        $this->transportResults[] = $message;
    }

    public function recordSdkError(string $message): void
    {
        $this->sdkErrors[] = $message;
    }

    private function maskDsn(string $dsn): string
    {
        return (string) preg_replace('#://([^@]+)@#', '://***@', $dsn);
    }
}
