<?php
namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Key
{
    const DEFAULT_SCOPE = 'main';

    static public function decode($key)
    {
        if(preg_match('~^(?<scope>[a-zA-Z0-9_\-;]*):(?<key>[a-zA-Z0-9_\-;]*)$~', $key, $match))
        {
            return array($match['scope'] ?: self::DEFAULT_SCOPE, $match['key'] ?: null);
        }
        else if(preg_match('~^(?<key>[a-zA-Z0-9_\-;]*)$~', $key, $match))
        {
            return array(self::DEFAULT_SCOPE, $match['key'] ?: null);
        }
        throw new InvalidKeyException;
    }

    static public function encode($scope, $key, $delimiter = ':')
    {
        return $scope . $delimiter . $key;
    }
}
