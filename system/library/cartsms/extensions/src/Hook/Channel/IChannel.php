<?php
namespace BulkGate\Extensions\Hook\Channel;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
interface IChannel
{
    /**
     * @return bool
     */
    public function isActive();

    /**
     * @return array
     */
    public function toArray();
}
