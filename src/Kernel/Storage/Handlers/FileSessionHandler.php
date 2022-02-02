<?php

namespace Fwt\Framework\Kernel\Storage\Handlers;

use Fwt\Framework\Kernel\FileLoader;
use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{
    protected string $path;
    protected int $lifetime;

    public function __construct(string $path, int $lifetime = null)
    {
        $this->path = $path;
        $this->lifetime = $lifetime ?? 15 * 60;
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id)
    {
        if (is_file($path = "$this->path/$id") && (time() - filemtime($path)) <= $this->lifetime) {
            return file_get_contents($path);
        }

        return '';
    }

    public function write($id, $data): bool
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        return file_put_contents("$this->path/$id", $data, LOCK_EX) !== false;
    }

    public function destroy($id): bool
    {
        if (is_file($path = "$this->path/$id")) {
            return unlink($path);
        }

        return true;
    }

    public function gc($max_lifetime)
    {
        $loader = new FileLoader();
        $loader->load($this->path);
        $count = 0;

        foreach ($loader->files() as $file) {
            if ((time() - filemtime($file)) >= $max_lifetime) {
                if (unlink($file) === false) {
                    return false;
                }

                $count++;
            }
        }

        return $count;
    }
}
