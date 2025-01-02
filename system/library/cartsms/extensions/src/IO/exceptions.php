<?php
namespace BulkGate\Extensions\IO;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class ConnectionException extends Extensions\Exception {}

class InvalidRequestException extends ConnectionException {}

class InvalidResultException extends ConnectionException {}

class AuthenticateException extends ConnectionException {}
