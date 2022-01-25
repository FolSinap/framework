<?php

namespace Fwt\Framework\Kernel\Console\Commands;

use Fwt\Framework\Kernel\Console\Input;

abstract class AbstractCommand implements Command
{
    public function getOptions(): array
    {
        return [];
    }

    public function getOptionalParameters(): array
    {
        return [];
    }

    public function getRequiredParameters(): array
    {
        return [];
    }

    protected function getParameters(Input $input): array
    {
        $definedParameters = array_merge($this->getRequiredParameters(), $this->getOptionalParameters());
        $input = $input->getParameters();

        $i = 0;
        $parameters = [];

        foreach ($definedParameters as $name => $description) {
            $parameters[$name] = $input[$i] ?? null;
            $i++;
        }

        return $parameters;
    }
}
