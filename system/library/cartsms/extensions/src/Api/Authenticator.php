<?php
namespace BulkGate\Extensions\Api;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Authenticator extends Extensions\Strict
{
    /** @var Extensions\Settings */
    private $settings;

    public function __construct(Extensions\ISettings $settings)
    {
        $this->settings = $settings;
    }

    public function authenticate($application_id, $application_token)
    {
        if($this->settings->load("static:application_id") === $application_id && $this->settings->load("static:application_token") === $application_token)
        {
            return true;
        }
        throw new ConnectionException('Unauthorized', 401);
    }
}
