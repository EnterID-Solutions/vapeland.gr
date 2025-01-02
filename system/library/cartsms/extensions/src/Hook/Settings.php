<?php
namespace BulkGate\Extensions\Hook;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Settings extends BulkGate\Extensions\Iterator
{
    public function __construct(array $data)
    {
        $settings = array();

        foreach($data as $type => $channel)
        {
            switch ($type)
            {
                case 'sms':
                    $settings[$type] = new BulkGate\Extensions\Hook\Channel\Sms((array) $channel);
                break;
                default:
                    $settings[$type] = new BulkGate\Extensions\Hook\Channel\DefaultChannel((array) $channel);
                break;
            }
        }

        parent::__construct($settings);
    }

    public function toArray()
    {
        $output = array();

        /** @var BulkGate\Extensions\Hook\Channel\IChannel $item */
        foreach($this->array as $key => $item)
        {
            if($item->isActive())
            {
                $output[$key] = $item->toArray();
            }
        }
        return $output;
    }
}
