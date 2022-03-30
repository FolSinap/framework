<?php

namespace FW\Kernel\Logging;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Redis;
use FW\Kernel\Exceptions\IllegalValueException;
use Monolog\Handler\FingersCrossedHandler;
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
        'redis' => RedisHandler::class,
        'fingers_crossed' => FingersCrossedHandler::class,
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
            $handlers[] = $this->createHandler($handlerName);
        }

        return $logger->setHandlers($handlers);
    }

    protected function createHandler(string $handlerName)
    {
        $handlerConfig = $this->config->get("handlers.$handlerName");
        $type = $handlerConfig['type'];
        unset($handlerConfig['type']);

        switch ($type) {
            case 'stream':
            case 'rotating_file':
            case 'syslog':
            case 'error_log':
            case 'process':
            case 'native_mail':
            case 'swift_mail':
            case 'pushover':
            case 'flowdock':
            case 'slack_webhook':
            case 'slack':
            case 'send_grid':
            case 'mandrill':
            case 'fleep':
            case 'ifttt':
            case 'telegram':
            case 'socket':
                return new (self::HANDLER_TYPES[$type])(...$handlerConfig);
            case 'redis':
                return new RedisHandler(
                    (new Redis(config('database.drivers.redis')))->getConnection(),
                    ...$handlerConfig
                );
            case 'fingers_crossed':
                $handlerConfig['handler'] = $this->createHandler($this->config->get("handlers.$handlerName.handler"));

                return new FingersCrossedHandler(...$handlerConfig);
            default:
                throw IllegalValueException::illegalValue($type, array_keys(self::HANDLER_TYPES), valueName: 'Handler type');
            }
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
