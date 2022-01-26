<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\Console\Commands\Command;

abstract class MakeCommand extends Command
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
        $baseDir = $this->getBaseDir();

        if (!is_dir($baseDir)) {
            mkdir($baseDir);
        }

        return file_put_contents("$baseDir/$name", $content);
    }
}
