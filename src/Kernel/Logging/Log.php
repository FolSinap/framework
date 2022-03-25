<?php

namespace FW\Kernel\Logging;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Redis;
use Monolog\Handler\FleepHookHandler;
use Monolog\Handler\FlowdockHandler;
use Monolog\Handler\IFTTTHandler;
use Monolog\Handler\MandrillHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\ProcessHandler;
use Monolog\Handler\PushoverHandler;
use Monolog\Handler\RedisHandler;
use Monolog\Handler\SendGridHandler;
use Monolog\Handler\SlackHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\TelegramBotHandler;
use Stringable;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Log implements LoggerInterface
{
    public const HANDLER_TYPES = [
        'stream' => StreamHandler::class,
        'rotating_file' => RotatingFileHandler::class,
        'syslog' => SyslogHandler::class,
        'error_log' => ErrorLogHandler::class,
        'process' => ProcessHandler::class,
        'native_mail' => NativeMailerHandler::class,
        'swift_mail' => SwiftMailerHandler::class,
        'pushover' => PushoverHandler::class,
        'flowdock' => FlowdockHandler::class,
        'slack_webhook' => SlackWebhookHandler::class,
        'slack' => SlackHandler::class,
        'send_grid' => SendGridHandler::class,
        'mandrill' => MandrillHandler::class,
        'fleep' => FleepHookHandler::class,
        'ifttt' => IFTTTHandler::class,
        'telegram' => TelegramBotHandler::class,
        'socket' => SocketHandler::class,
    ];

    /** @var Logger[] $loggers */
    protected array $loggers;
    protected FileConfig $config;

    public function __construct(array $channels = [])
    {
        $this->config = config('logs');
        $defaultChannels = config('logs.channels');
        $loggers = [];

        foreach ($channels as $channel) {
            $loggers[$channel] = $this->createLogger($channel);
        }

        if (empty($loggers)) {
            foreach ($defaultChannels as $channel) {
                $loggers[$channel] = $this->createLogger($channel);
            }
        }

        $this->loggers = $loggers;
    }

    public static function channels(string|array $channels): self
    {
        return new self(is_string($channels) ? [$channels] : $channels);
    }

    protected function createLogger(string $channel): Logger
    {
        $handlerNames = $this->config->get("channels.$channel");
        $logger = new Logger($channel);
        $handlers = [];

        foreach ($handlerNames as $handlerName) {
            $handlerConfig = $this->config->get("handlers.$handlerName");
            $type = $handlerConfig['type'];
            unset($handlerConfig['type']);

            $handlers[] = match ($type) {
                'stream' => new StreamHandler (...$handlerConfig),
                'rotating_file' => new RotatingFileHandler (...$handlerConfig),
                'syslog' => new SyslogHandler (...$handlerConfig),
                'error_log' => new ErrorLogHandler (...$handlerConfig),
                'process' => new ProcessHandler (...$handlerConfig),
                'native_mail' => new NativeMailerHandler (...$handlerConfig),
                'swift_mail' => new SwiftMailerHandler (...$handlerConfig),
                'pushover' => new PushoverHandler (...$handlerConfig),
                'flowdock' => new FlowdockHandler (...$handlerConfig),
                'slack_webhook' => new SlackWebhookHandler (...$handlerConfig),
                'slack' => new SlackHandler (...$handlerConfig),
                'send_grid' => new SendGridHandler (...$handlerConfig),
                'mandrill' => new MandrillHandler (...$handlerConfig),
                'fleep' => new FleepHookHandler (...$handlerConfig),
                'ifttt' => new IFTTTHandler (...$handlerConfig),
                'telegram' => new TelegramBotHandler (...$handlerConfig),
                'socket' => new SocketHandler (...$handlerConfig),
                'redis' => new RedisHandler((new Redis())->getConnection(), $handlerConfig['key'])
            };
//            $handlers[] = new (self::HANDLER_TYPES[$type])(...$handlerConfig);
        }

        return $logger->setHandlers($handlers);
    }

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::EMERGENCY, $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::ALERT, $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::CRITICAL, $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::ERROR, $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::WARNING, $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::NOTICE, $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::INFO, $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log(Logger::DEBUG, $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->addRecord($level, $message, $context);
        }
    }
}
