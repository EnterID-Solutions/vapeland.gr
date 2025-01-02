<?php
namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
interface ISettings
{
    /**
     * @param string $settings_key
     * @param bool $default
     * @return mixed
     */
    public function load($settings_key, $default = false);

    /**
     * @param string $settings_key
     * @param mixed|$value
     * @param array $meta
     */
    public function set($settings_key, $value, array $meta = array());

    /**
     * @param string|null $settings_key
     */
    public function delete($settings_key = null);

    /**
     * @return array
     */
    public function synchronize();

    /**
     * @return void
     */
    public function install();

    /**
     * @return void
     */
    public function uninstall();
}
