<?php

namespace FW\Kernel\Logging;

use FW\Kernel\Config\FileConfig;
use FW\Kernel\Database\Redis;
use FW\Kernel\Exceptions\IllegalValueException;
use FW\Kernel\Exceptions\InvalidExtensionException;
use FW\Kernel\ObjectResolver;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FleepHookHandler;
use Monolog\Handler\FlowdockHandler;
use Monolog\Handler\HandlerInterface;
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
        'buffer' => BufferHandler::class,
    ];
    public const FORMATTERS = [
        'line' => LineFormatter::class,
        'html' => HtmlFormatter::class,
        'normalizer' => NormalizerFormatter::class,
        'scalar' => ScalarFormatter::class,
        'json' => JsonFormatter::class,
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

    protected function createHandler(string $handlerName): HandlerInterface
    {
        $handlerConfig = $this->config->get("handlers.$handlerName");
        $type = $handlerConfig['type'];
        $formatter = $handlerConfig['formatter'] ?? null;
        unset($handlerConfig['type'], $handlerConfig['formatter']);

        switch ($type) {
            case 'redis':
                $handlerConfig['redis'] = (new Redis(config('database.drivers.redis')))->getConnection();
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
                $handler = new (self::HANDLER_TYPES[$type])(...$handlerConfig);

                break;
            case 'fingers_crossed':
            case 'buffer':
                $handlerConfig['handler'] = $this->createHandler($this->config->get("handlers.$handlerName.handler"));
                $handler = new (self::HANDLER_TYPES[$type])(...$handlerConfig);

                break;
            default:
                if (class_exists($type) && in_array(HandlerInterface::class, class_implements($type))) {
                    $resolver = container(ObjectResolver::class);

                    return $resolver->resolve($type);
                }

                throw IllegalValueException::illegalValue($type, array_keys(self::HANDLER_TYPES), valueName: 'Handler type');
        }

        if (isset($formatter)) {
            $handler->setFormatter($this->createFormatter($formatter));
        }

        return $handler;
    }

    protected function createFormatter(string $formatter): FormatterInterface
    {
        switch ($formatter) {
            case 'line':
            case 'json':
            case 'html':
            case 'normalizer':
            case 'scalar':
                return new (self::FORMATTERS[$formatter])();
            default:
                if (class_exists($formatter) && in_array(FormatterInterface::class, class_implements($formatter))) {
                    $resolver = container(ObjectResolver::class);

                    return $resolver->resolve($formatter);
                }

                throw IllegalValueException::illegalValue($formatter, array_keys(self::FORMATTERS), valueName: 'Formatter');
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
