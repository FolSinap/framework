<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\Console\App;
use Fwt\Framework\Kernel\Console\Commands\Command;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\TextBuilder;
use Fwt\Framework\Kernel\Console\Output\Output;

class MakeCommandCommand extends MakeCommand
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

    public function getOptions(): array
    {
        return [
            'make' => ['Create make command (different inheritance).', 'm']
        ];
    }

    public function getOptionalParameters(): array
    {
        return [
            'name' => 'Command name.',
            'description' => 'Command description.',
        ];
    }

    public function make(Input $input, Output $output): void
    {
        $fullClassName = $this->getParameters($input)['class_name'];
        $description = $this->getParameters($input)['description'] ?? '';
        $name = $this->getParameters($input)['name'];
        $namespace = ltrim(App::$app->getConfig('app.commands.namespace'), '\\');
        $isMake = (bool) $input->getOption('make', 'm');

        [$className, $namespace] = $this->normalizeClassAndNamespace($fullClassName, $namespace);

        if ($isMake) {
            $class = MakeCommand::class;
            $additionalMethods = $this->renderMethodsForMakeCommand();
        } else {
            $class = Command::class;
            $additionalMethods = $this->renderExecuteMethod();
        }

        $use = "use $class;";
        $class = explode('\\', $class);
        $extends = array_pop($class);

        if (!$name) {
            $name = $this->defaultNameFromClass($className);
        }

        $this->stubReplacements = compact(
            'namespace',
            'className',
            'description',
            'name',
            'use',
            'extends',
            'additionalMethods'
        );

        $this->fileName = "$fullClassName.php";

        $this->successful = 'New command has been successfully created.';
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

    protected function renderExecuteMethod(): string
    {
        return TextBuilder::getBuilder()
            ->skipLines(2)
            ->tab()->writeln('public function execute(Input $input, Output $output): void')
            ->writeln('{')
            ->tab()->writeln('// TODO: Implement execute() method.')
            ->dropTab()->write('}');
    }

    protected function renderMethodsForMakeCommand(): string
    {
        return TextBuilder::getBuilder()
            ->skipLines(2)
                ->tab()->writeln('protected function getBaseDir(): string')
                ->writeln('{')
                ->tab()->writeln('// TODO: Implement getBaseDir() method.')
                ->dropTab()->write('}')
            ->skipLines(2)
                ->writeln('protected function getStubFile(): string')
                ->writeln('{')
                ->tab()->writeln('// TODO: Implement getStubFile() method.')
                ->dropTab()->write('}')
            ->skipLines(2)
                ->writeln('protected function make(Input $input, Output $output): void')
                ->writeln('{')
                ->tab()->writeln('// TODO: Implement make() method.')
                    ->skipLines()
                    ->writeln('// Don\'t forget to set properties')
                    ->writeln('$this->stubReplacements = [];')
                    ->writeln('$this->fileName = \'\';')
                ->dropTab()->write('}');
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
