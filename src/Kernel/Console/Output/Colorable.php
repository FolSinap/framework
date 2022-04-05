<?php

namespace FW\Kernel\Console\Output;

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
        $this->text .= self::GREEN;

        return $this;
    }

    public function startBlack(): self
    {
        $this->text .= self::BLACK;

        return $this;
    }

    public function startGray(): self
    {
        $this->text .= self::GRAY;

        return $this;
    }

    public function startRed(): self
    {
        $this->text .= self::RED;

        return $this;
    }

    public function startYellow(): self
    {
        $this->text .= self::YELLOW;

        return $this;
    }

    public function startBlue(): self
    {
        $this->text .= self::BLUE;

        return $this;
    }

    public function startWhite(): self
    {
        $this->text .= self::WHITE;

        return $this;
    }

    public function onBlack(): self
    {
        $this->text .= self::ON_BLACK;

        return $this;
    }

    public function onRed(): self
    {
        $this->text .= self::ON_RED;

        return $this;
    }

    public function onGreen(): self
    {
        $this->text .= self::ON_GREEN;

        return $this;
    }

    public function onYellow(): self
    {
        $this->text .= self::ON_YELLOW;

        return $this;
    }

    public function onBlue(): self
    {
        $this->text .= self::ON_BLUE;

        return $this;
    }

    public function onWhite(): self
    {
        $this->text .= self::ON_WHITE;

        return $this;
    }

    public function closeColor(): self
    {
        $this->text .= self::CLOSE_COLOR;

        return $this;
    }
}
