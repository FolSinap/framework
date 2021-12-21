<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\Console\Commands\AbstractCommand;

abstract class AbstractMakeCommand extends AbstractCommand
{
    abstract protected function getBaseDir(): string;

    abstract protected function getStubFile(): string;

    protected function replaceStubTemplates(array $replacements): string
    {
        $stub = file_get_contents($this->getStubFile());

        foreach ($replacements as $template => $replacement) {
            $stub = preg_replace_callback("/{{\|($template)\|}}/", function () use ($replacement) {
                return $replacement;
            }, $stub);
        }

        return $stub;
    }

    protected function createFile(string $name, string $content): bool
    {
        return file_put_contents($this->getBaseDir() . "/$name", $content);
    }
}
