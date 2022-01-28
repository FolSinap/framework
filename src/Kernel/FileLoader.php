<?php

namespace Fwt\Framework\Kernel;

use Fwt\Framework\Kernel\Exceptions\FileSystem\FileReadException;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;

class FileLoader
{
    protected array $files = [];

    public function load(string $dir): void
    {
        if (!is_dir($dir)) {
            //todo: questionable decision
            return;
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $fullPath = "$dir/$file";

            if (is_dir($fullPath)) {
                $this->load($fullPath);
            } else {
                $this->files[$file] = $fullPath;
            }
        }
    }

    public function files(): array
    {
        return $this->files;
    }

    public function baseNames(): array
    {
        return array_keys($this->files);
    }

    public function classNames(): array
    {
        $classes = [];

        foreach ($this->files as $file) {
            if (file_exists($file)) {
                $source = fopen($file, 'r');

                if ($source === false) {
                    throw new FileReadException($file);
                }

                $classes[] = $this->getClassName($source);
            }
        }

        return $classes;
    }

    public function requireAll(): void
    {
        foreach ($this->files as $file) {
            require $file;
        }
    }

    public function requireOnceAll(): void
    {
        foreach ($this->files as $file) {
            require_once $file;
        }
    }

    public function includeAll()
    {
        foreach ($this->files as $file) {
            include $file;
        }
    }

    protected function getClassName($source): string
    {
        if (!is_resource($source)) {
            throw new IllegalTypeException($source, ['resource']);
        }

        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($source)) {
                break;
            }

            $buffer .= fread($source, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }

        return "$namespace\\$class";
    }
}
