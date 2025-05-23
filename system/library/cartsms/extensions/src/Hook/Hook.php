<?php
namespace BulkGate\Extensions\Hook;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Hook extends BulkGate\Extensions\Strict
{
    /** @var string */
    private $url;

    /** @var string */
    private $language_iso;

    /** @var int */
    private $shop_id;

    /** @var BulkGate\Extensions\IO\IConnection */
    private $connection;

    /** @var BulkGate\Extensions\ISettings */
    private $settings;

    /** @var ILoad */
    private $load;

    public function __construct($url, $language_iso, $shop_id, BulkGate\Extensions\IO\IConnection $connection, BulkGate\Extensions\ISettings $settings, ILoad $load)
    {
        $this->url = $url;
        $this->language_iso = $settings->load('main:language_mutation', false) ? (string) $language_iso : 'default';
        $this->shop_id = (int) $shop_id;
        $this->connection = $connection;
        $this->settings = $settings;
        $this->load = $load;
    }

    public function run($name, Variables $variables)
    {
        $customer = new Settings((array) $this->settings->load($this->getKey($name, 'customer'), array()));
        $admin = new Settings((array) $this->settings->load($this->getKey($name, 'admin'), array()));

        if(count($customer->toArray()) > 0 || count($admin->toArray()) > 0)
        {
            $this->load->load($variables);

            return $this->connection->run(new BulkGate\Extensions\IO\Request($this->url, array(
                'customer_sms' => $customer->toArray(),
                'admin_sms' => $admin->toArray(),
                'variables' => $variables->toArray()
            ), true, 5));
        }
        return false;
    }

    private function getKey($name, $type)
    {
        return $type.'_sms-'.($type === 'admin' ? 'default' : $this->language_iso).'-'.$this->shop_id.':'.$name;
    }
}
