<?php

namespace Fwt\Framework\Kernel;

use Fwt\Framework\Kernel\Response\Response;

class Pipeline
{
    protected Request $request;
    protected array $pipes;

    public function send(Request $request): self
    {
        $this->request = $request;

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
            $response = $pipe($this->request);

            if ($response instanceof Request) {
                $this->request = $response;
            } elseif ($response instanceof Response) {
                return $response;
            }
        }

        return $this->request;
    }
}
