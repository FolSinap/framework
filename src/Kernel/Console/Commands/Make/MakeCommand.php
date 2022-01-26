<?php

namespace Fwt\Framework\Kernel\Console\Commands\Make;

use Fwt\Framework\Kernel\Console\Commands\Command;
use Fwt\Framework\Kernel\Console\Input;
use Fwt\Framework\Kernel\Console\Output\Output;

abstract class MakeCommand extends Command
{
    protected array $stubReplacements = [];
    protected string $fileName;
    protected string $successful;

    abstract protected function getBaseDir(): string;

    abstract protected function getStubFile(): string;

    abstract protected function make(Input $input, Output $output): void;

    public function execute(Input $input, Output $output): void
    {
        $this->make($input, $output);
        $this->checkProperties();

        $stub = $this->replaceStubTemplates($this->stubReplacements);

        //todo: check for errors after createFile
        if ($this->createFile($this->fileName, $stub)) {
            $output->success($this->successful ?? "File $this->fileName has been successfully created.");
        } else {
            $output->error('Something went wrong');
        }
    }

    private function checkProperties(): void
    {
        if (!isset($this->fileName)) {
            throw new \Exception('property fileName should be set inside make method');
        }
    }

    private function createFile(string $name, string $content): bool
    {
        $baseDir = $this->getBaseDir();

        if (!is_dir($baseDir)) {
            mkdir($baseDir);
        }

        return file_put_contents("$baseDir/$name", $content);
    }

    private function replaceStubTemplates(array $replacements): string
    {
        $stub = file_get_contents($this->getStubFile());

        foreach ($replacements as $template => $replacement) {
            $stub = preg_replace_callback("/{{\|($template)\|}}/", function () use ($replacement) {
                return $replacement;
            }, $stub);
        }

        return $stub;
    }
}
