<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;

class MakeCommandCommand extends AbstractMakeCommand
{
    public function getName(): string
    {
        return 'make:command';
    }

    public function getDescription(): string
    {
        return 'Create new command class.';
    }

    public function getRequiredParameters(): array
    {
        return [
            'class_name' => 'Name of Command class.',
        ];
    }

    public function getOptionalParameters(): array
    {
        return [
            'name' => 'Command name.',
            'description' => 'Command description.',
        ];
    }

    //todo: add --make option
    public function execute(Input $input, Output $output): void
    {
        $className = $this->getParameters($input)['class_name'];
        $description = $this->getParameters($input)['description'] ?? '';
        $name = $this->getParameters($input)['name'];
        $namespace = ltrim(App::$app->getConfig('app.commands.namespace'), '\\');

        if (!$name) {
            $name = $this->defaultNameFromClass($className);
        }

        $stub = $this->replaceStubTemplates([
            'namespace' => $namespace,
            'class_name' => $className,
            'description' => $description,
            'name' => $name,
        ]);

        $this->createFile("$className.php", $stub);

        $output->success('Created new command successfully.');
    }

    protected function defaultNameFromClass(string $className): string
    {
        $className = lcfirst($className);

        if (str_ends_with($className, 'Command')) {
            $className = substr($className, 0, -strlen('Command'));
        }

        $name = '';

        foreach (str_split($className) as $char) {
            if (ctype_upper($char)) {
                $char = ':' . strtolower($char);
            }

            $name .= $char;
        }

        return $name;
    }

    protected function getBaseDir(): string
    {
        return App::$app->getConfig('app.commands.dir');
    }

    protected function getStubFile(): string
    {
        return __DIR__ . '/stubs/Command.stub';
    }
}
