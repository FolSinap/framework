<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;

class UniqueRule implements Rule
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
