<?php
namespace BulkGate\Extensions\Hook\Channel;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class DefaultChannel extends \stdClass implements IChannel
{
    /** @var bool */
    public $active = false;

    /** @var string */
    public $template = '';

    /** @var bool */
    public $customer = false;

    /** @var array */
    public $admins = array();

    public function __construct(array $data)
    {
        foreach($data as $key => $value)
        {
            $this->{$key} = $value;
        }
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function toArray()
    {
        return (array) $this;
    }
}
