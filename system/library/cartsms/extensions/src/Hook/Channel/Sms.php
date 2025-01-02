<?php
namespace BulkGate\Extensions\Hook\Channel;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Sms extends BulkGate\Extensions\Strict implements IChannel
{
    /** @var bool */
    private $active = false;

    /** @var string */
    private $template = '';

    /** @var bool */
    private $unicode = false;

    /** @var bool */
    private $flash = false;

    /** @var string */
    private $senderType = "gSystem";

    /** @var string */
    private $senderValue = "";

    /** @var bool */
    private $customer = false;

    /** @var array */
    private $admins = array();

    public function __construct(array $data)
    {
        foreach($data as $key => $value)
        {
            try
            {
                $this->{$key} = $value;
            }
            catch (BulkGate\Extensions\StrictException $e)
            {
            }
        }
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function toArray()
    {
        return array(
            'active'         => (bool) $this->active,
            'template'       => (string) $this->template,
            'unicode'        => (bool) $this->unicode,
            'flash'          => (bool) $this->flash,
            'senderType'     => (string) $this->senderType,
            'senderValue'    => (string) $this->senderValue,
            'customer'       => (bool) $this->customer,
            'admins'         => (array) $this->admins
        );
    }
}
