<?php
namespace BulkGate\Extensions\Api;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
abstract class Api extends Extensions\Strict
{
    /** @var Extensions\Database\IDatabase */
    protected $database;

    /** @var Extensions\ISettings */
    protected $settings;

    public function __construct($action, Extensions\Api\IRequest $data, Extensions\Database\IDatabase $database, Extensions\ISettings $settings)
    {
        $this->database = $database;
        $this->settings = $settings;

        $method = 'action'.ucfirst($action);

        if(method_exists($this, $method))
        {
            call_user_func_array(array($this, $method), array($data));
        }
        else
        {
            throw new ConnectionException('Not Found', 404);
        }
    }

    public function sendResponse(IResponse $response)
    {
        $response->send();
        exit;
    }
}
