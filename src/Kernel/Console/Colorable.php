<?php

namespace Fwt\Framework\Kernel\Console;

trait Colorable
{
    public function green(string $message): self
    {
        $this->startGreen();
        $this->type($message);
        $this->closeColor();

        return $this;
    }

    public function blue(string $message): self
    {
        $this->startBlue();
        $this->type($message);
        $this->closeColor();

        return $this;
    }

    public function startGreen(): self
    {
        $this->message .= self::GREEN;

        return $this;
    }

    public function startBlack(): self
    {
        $this->message .= self::BLACK;

        return $this;
    }

    public function startRed(): self
    {
        $this->message .= self::RED;

        return $this;
    }

    public function startYellow(): self
    {
        $this->message .= self::YELLOW;

        return $this;
    }

    public function startBlue(): self
    {
        $this->message .= self::BLUE;

        return $this;
    }

    public function startWhite(): self
    {
        $this->message .= self::WHITE;

        return $this;
    }

    public function onBlack(): self
    {
        $this->message .= self::ON_BLACK;

        return $this;
    }

    public function onRed(): self
    {
        $this->message .= self::ON_RED;

        return $this;
    }

    public function onGreen(): self
    {
        $this->message .= self::ON_GREEN;

        return $this;
    }

    public function onYellow(): self
    {
        $this->message .= self::ON_YELLOW;

        return $this;
    }

    public function onBlue(): self
    {
        $this->message .= self::ON_BLUE;

        return $this;
    }

    public function onWhite(): self
    {
        $this->message .= self::ON_WHITE;

        return $this;
    }

    public function closeColor(): self
    {
        $this->message .= self::CLOSE_COLOR;

        return $this;
    }
}