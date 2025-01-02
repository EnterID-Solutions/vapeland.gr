<?php
namespace BulkGate\Extensions\IO;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class ConnectionFactory extends BulkGate\Extensions\Strict
{
    /** @var BulkGate\Extensions\ISettings */
    private $settings;

    /** @var IConnection */
    private $io;

    public function __construct(BulkGate\Extensions\ISettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $url
     * @param string $product
     * @return IConnection
     */
    public function create($url, $product)
    {
        if($this->io === null)
        {
            if(extension_loaded('curl'))
            {
                $this->io = new cUrl($this->settings->load("static:application_id"), $this->settings->load("static:application_token"), $url, $product, $this->settings->load('main:language', 'en'));
            }
            else
            {
                $this->io = new FSock($this->settings->load("static:application_id"), $this->settings->load("static:application_token"), $url, $product, $this->settings->load('main:language', 'en'));
            }
        }
        return $this->io;
    }
}
