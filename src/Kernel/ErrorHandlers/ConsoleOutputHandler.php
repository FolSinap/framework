<?php

namespace FW\Kernel\ErrorHandlers;

use FW\Kernel\Console\Output\MessageBuilder;
use Whoops\Exception\Frame;
use Whoops\Handler\PlainTextHandler;

class ConsoleOutputHandler extends PlainTextHandler
{
    private bool $addPreviousToOutput = true;

    private function getTraceOutput()
    {
        if (!$this->addTraceToOutput()) {
            return '';
        }

        $inspector = $this->getInspector();
        $frames = $inspector->getFrames();

        $line = 1;

        return MessageBuilder::getBuilder()
            ->nextLine()
            ->write('Stack trace:')
            ->foreach($frames, function (int $index, Frame $frame) use (&$line) {
                $class = $frame->getClass();
                $function = $frame->getFunction();

                $template = MessageBuilder::getBuilder()
                    ->skipLines()->type('%3d.')
                    ->startBlue()->type(' %s')
                    ->startYellow()
                    ->if(!$class, '%s',
                        MessageBuilder::getBuilder()
                            ->if(isset($function), MessageBuilder::getBuilder()->startWhite()->type('->'))
                            ->startYellow()->type('%s')
                    )
                    ->if(isset($function), MessageBuilder::getBuilder()->startWhite()->type('()'))
                    ->startGreen()->type(' %s')
                    ->closeColor()->type(':')
                    ->startBlue()->type('%d')->closeColor()->getMessage();

                return MessageBuilder::getBuilder()
                    ->write(sprintf(
                        $template,
                        $line++,
                        $class,
                        $function,
                        $frame->getFile(),
                        $frame->getLine(),
                    ));
            });
    }

    private function getExceptionOutput($exception): string
    {
        $template = MessageBuilder::getBuilder()
            ->startRed()->type('%s:')->closeColor()
            ->startYellow()->type(' %s')->closeColor()
            ->type(' in file')
            ->startGreen()->type(' %s')->closeColor()
            ->type(' on line')
            ->startBlue()->type(' %d')->closeColor()
            ->getMessage();

        return sprintf(
            $template,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
    }

    /** Copied from parent class so we can use overridden private methods up above */
    public function generateResponse(): string
    {
        $exception = $this->getException();
        $message = $this->getExceptionOutput($exception);

        if ($this->addPreviousToOutput) {
            $previous = $exception->getPrevious();
            while ($previous) {
                $message .= "\n\nCaused by\n" . $this->getExceptionOutput($previous);
                $previous = $previous->getPrevious();
            }
        }

        return $message . $this->getTraceOutput() . "\n";
    }
}
