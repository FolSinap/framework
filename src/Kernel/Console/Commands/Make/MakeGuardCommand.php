<?php

namespace FW\Kernel\Console\Commands\Make;

use FW\Kernel\Console\Input;
use FW\Kernel\Console\Output\Output;
use FW\Kernel\Console\TextBuilder;

class MakeGuardCommand extends MakeCommand
{
    public function getName(): string
    {
        return 'make:guard';
    }

    public function getDescription(): string
    {
        return 'Create new Guard class.';
    }

    public function getRequiredParameters(): array
    {
        return [
            'class name' => 'Name of Guard class.',
        ];
    }

    public function getOptionalParameters(): array
    {
        return [
            'guard name' => 'Name of the guard (full class name by default).',
        ];
    }

    protected function getBaseDir(): string
    {
        return config('app.guards.dir');
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/Guard.stub';
    }

    protected function make(Input $input, Output $output): void
    {
        $className = $this->getParameters($input)['class name'];
        $name = $this->getParameters($input)['guard name'];

        [$className, $namespace] = $this->normalizeClassAndNamespace(
            $className,
            ltrim(config('app.guards.namespace'), '\\')
        );

        $this->stubReplacements = [
            'className' => $className,
            'namespace' => $namespace,
            'getNameFunc' => $this->renderGetNameMethod($name),
        ];
        $this->fileName = "$className.php";
        $this->successful = 'New Guard class has been successfully created.';
    }

    protected function renderGetNameMethod(?string $name): string
    {
        if (is_null($name)) {
            return '';
        }

        return TextBuilder::getBuilder()
            ->nextLine()
            ->tab()->writeln('public function getName(): string')
            ->writeln('{')
            ->tab()->writeln("return '$name';")
            ->dropTab()->write('}');
    }
}
