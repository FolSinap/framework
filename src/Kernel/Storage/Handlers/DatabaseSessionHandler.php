<?php

namespace FW\Kernel\Storage\Handlers;

use FW\Kernel\Database\Database;
use FW\Kernel\Database\ORM\Models\AnonymousModel;
use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    protected Database $connection;
    protected int $lifetime;

    public function __construct(Database $connection, int $lifetime = null, string $table = null)
    {
        $this->connection = $connection;
        $this->lifetime = $lifetime ?? 15 * 60;
        $table = $table ?? 'sessions';

        AnonymousModel::setTableName($table);
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy($id): bool
    {
        $this->connection->delete(AnonymousModel::getTableName())->where('id', $id);
        $this->connection->execute();

        return true;
    }

    public function gc($max_lifetime): bool
    {
        $this->connection->delete(AnonymousModel::getTableName())
            ->where('updated_at', date('Y-m-d H:i:s', time() - $this->lifetime), '<');
        $this->connection->execute();

        return true;
    }

    public function read($id)
    {
        $session = AnonymousModel::find($id);

        if (strtotime($session->updated_at) < (time() - $this->lifetime)) {
            return '';
        }

        return $session->payload ?? '';
    }

    public function write($id, $data): bool
    {
        if ($session = AnonymousModel::find($id)) {
            $session->update(['payload' => $data]);
        } else {
            AnonymousModel::create(['id' => $id, 'payload' => $data]);
        }

        return true;
    }
}
