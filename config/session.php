<?php

return [
    'driver' => 'database', //null, files, redis
    'lifetime' => 15 * 60, //seconds, 15 min by default

    'filepath' => 'storage/session', //for 'files' session driver
    'table' => 'sessions', //for 'database' session driver
];
