<?php
namespace BulkGate\Extensions\Api;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
interface IResponse
{
    /**
     * @return void
     */
    public function send();
}
