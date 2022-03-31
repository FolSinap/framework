<?php

namespace FW\Kernel\Logging;

use Carbon\CarbonImmutable;
use FW\Kernel\Database\ORM\ModelCollection;
use FW\Kernel\Database\ORM\ModelRepository;
use Monolog\Handler\AbstractProcessingHandler;

class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @inheritDoc
     */
    protected function write(array $record): void
    {
        LogModel::create([
            'channel' => $record['channel'],
            'message' => $record['formatted'],
            'level' => $record['level'],
            'time' => CarbonImmutable::createFromInterface($record['datetime']),
        ]);
    }

    public function handleBatch(array $records): void
    {
        $models = new ModelCollection();

        foreach ($records as $record) {
            if ($this->processors) {
                $record = $this->processRecord($record);
            }

            $record['formatted'] = $this->getFormatter()->format($record);

            $models[] = LogModel::createDry([
                'channel' => $record['channel'],
                'message' => $record['formatted'],
                'level' => $record['level'],
                'time' => CarbonImmutable::createFromInterface($record['datetime']),
            ]);
        }

        (new ModelRepository())->insertMany($models);
    }
}
