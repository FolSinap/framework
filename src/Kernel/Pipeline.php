<?php

namespace Fwt\Framework\Kernel;

class Pipeline
{
    protected $value;
    protected array $pipes;

    public function send($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function through(array $pipes): self
    {
        foreach ($pipes as $pipe) {
            $this->addPipe($pipe);
        }

        return $this;
    }

    public function addPipe(callable $pipe): self
    {
        $this->pipes[] = $pipe;

        return $this;
    }

    public function go()
    {
        foreach ($this->pipes as $pipe) {
            $this->value = $pipe($this->value);
        }

        return $this->value;
    }
}
