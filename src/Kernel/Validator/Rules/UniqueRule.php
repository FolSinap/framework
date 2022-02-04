<?php

namespace FW\Kernel\Validator\Rules;

use FW\Kernel\App;
use FW\Kernel\Database\Database;

class UniqueRule implements IRule
{
    protected string $errorMessage = 'This value already exists.';
    protected string $column;
    protected string $table;

    public function __construct(string $column, string $table, string $errorMessage = null)
    {
        $this->column = $column;
        $this->table = $table;

        if ($errorMessage) {
            $this->errorMessage = $errorMessage;
        }
    }

    public function validate($value): bool
    {
        /** @var Database $database */
       $database = App::$app->getContainer()->get(Database::class);

       $database
           ->select($this->table)
           ->where($this->column, (string) $value);

       if (empty($database->fetchAssoc())) {
           return true;
       }

       return false;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
