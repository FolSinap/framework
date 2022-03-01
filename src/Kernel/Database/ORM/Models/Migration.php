<?php

namespace FW\Kernel\Database\ORM\Models;

class Migration extends Model
{
    public static function getColumns(): array
    {
        return ['id', 'name'];
    }
}
