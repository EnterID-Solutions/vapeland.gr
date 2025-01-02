<?php

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost')
{
    if(file_exists(__DIR__.'/../../../../../Tracy/tracy.php'))
    {
        error_reporting(1);
        require_once __DIR__.'/../../../../../Tracy/tracy.php';
        Tracy\Debugger::$strictMode = true;
        Tracy\Debugger::$maxDepth = 10;
        Tracy\Debugger::enable();
    }

    define('BULKGATE_DEBUG', 'http://localhost/bulkgate');
    define('BULKGATE_DEV_MODE', true);
}
