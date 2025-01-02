<?php
namespace BulkGate\Extensions\Api;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Response extends BulkGate\Extensions\Strict implements IResponse
{
    /** @var mixed */
    private $payload;

    /** @var string */
    private $contentType;

    public function __construct($payload, $compressed = false)
    {
        $this->payload = $payload;
        $this->contentType = $compressed ? 'application/zip' : 'application/json';
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function send()
    {
        header("Content-Type: {$this->contentType}; charset=utf-8");

        if($this->contentType === 'application/zip')
        {
            echo BulkGate\Extensions\Compress::compress(BulkGate\Extensions\Json::encode($this->payload));
        }
        else
        {
            echo BulkGate\Extensions\Json::encode($this->payload);
        }
    }
}
