<?php

namespace Fwt\Framework\Kernel;

use Fwt\Framework\Kernel\Exceptions\FileSystem\FileLoaderException;
use Fwt\Framework\Kernel\Exceptions\FileSystem\FileReadException;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use ReflectionClass;

class FileLoader
{
    protected array $files = [];
    protected bool $ignoreHidden = false;
    protected array $allowedExtensions = [];
    protected array $forbiddenExtensions = [];
    protected string $namePattern;

    public function refresh(): self
    {
        $this->ignoreHidden = false;
        $this->files = [];
        $this->allowedExtensions = [];
        $this->forbiddenExtensions = [];
        unset($this->namePattern);

        return $this;
    }

    public function ignoreHidden(bool $ignoreHidden = true): self
    {
        $this->ignoreHidden = $ignoreHidden;

        return $this;
    }

    public function allowedExtensions(array $extensions): self
    {
        $this->allowedExtensions = array_merge($this->allowedExtensions, $extensions);

        return $this;
    }

    public function forbiddenExtensions(array $extensions): self
    {
        $this->forbiddenExtensions = array_merge($this->forbiddenExtensions, $extensions);

        return $this;
    }

    public function setNamePattern(string $pattern): self
    {
        $this->namePattern = $pattern;

        return $this;
    }

    public function load(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new FileLoaderException(sprintf('%s is not a directory.', $dir));
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
                if (!$this->validateFile($file)) {
                    continue;
                }

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

    public function concreteClasses(): array
    {
        return $this->filterClasses($this->classNames(), true);
    }

    public function abstractClasses(): array
    {
        return $this->filterClasses($this->classNames(), false);
    }

    protected function filterClasses(array $classes, bool $concrete): array
    {
        $filtered = [];

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() && !$concrete) {
                $filtered[] = $class;
            } elseif (!$reflection->isAbstract() && $concrete) {
                $filtered[] = $class;
            }
        }

        return $filtered;
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

                if (!is_null($class = $this->getClassName($source))) {
                    $classes[] = $class;
                }
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

    protected function validateFile(string $file): bool
    {
        if ($this->ignoreHidden && str_starts_with($file, '.')) {
            return false;
        }

        if (isset($this->namePattern) && !preg_match($this->namePattern, $file)) {
            return false;
        }

        foreach ($this->forbiddenExtensions as $extension) {
            if (str_ends_with($file, $extension)) {
                return false;
            }
        }

        foreach ($this->allowedExtensions as $extension) {
            if (!str_ends_with($file, $extension)) {
                return false;
            }
        }

        return true;
    }

    protected function getClassName($source): ?string
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

                if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }

        if ($namespace === '' || $class === '') {
            return null;
        }

        return "$namespace\\$class";
    }
}
