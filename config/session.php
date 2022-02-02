<?php

return [
    'driver' => 'redis', //null, files, redis
    'filepath' => 'storage/session', //for 'files' session driver
    'lifetime' => 15 * 60, //seconds, 15 min by default
];
