<?php
namespace BulkGate\Extensions;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @method Settings getSettings()
 * @method IO\IConnection getConnection()
 * @method Translator getTranslator()
 * @method Synchronize getSynchronize()
 * @method ProxyActions getProxy()
 */
abstract class DIContainer extends Strict
{

    /** @var array */
    protected $services = array();


    /**
     * @return Database\IDatabase
     */
    abstract protected function createDatabase();


    /**
     * @return IModule
     */
    abstract protected function createModule();


    /**
     * @return ICustomers
     */
    abstract protected function createCustomers();


    /**
     * @return Settings
     */
    protected function createSettings()
    {
        return new Settings($this->getService('database'));
    }


    /**
     * @return IO\IConnection
     */
    protected function createConnection()
    {
        /** @var IModule $module */
        $module = $this->getService('module');

        $factory = new IO\ConnectionFactory($this->getService('settings'));

        return $factory->create($module->url(), $module->product());
    }


    /**
     * @return Translator
     */
    protected function createTranslator()
    {
        return new Translator($this->getService('settings'));
    }


    /**
     * @return Synchronize
     */
    protected function createSynchronize()
    {
        return new Synchronize($this->getService('settings'), $this->getService('connection'));
    }


    /**
     * @return ProxyActions
     */
    protected function createProxy()
    {
        return new ProxyActions($this->getService('connection'), $this->getService('module'), $this->getService('synchronize'), $this->getService('settings'), $this->getService('translator'), $this->getService('customers'));
    }


    /**
     * @param $name
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function getService($name)
    {
        $name = ucfirst($name);

        if(isset($this->services[$name]))
        {
            return $this->services[$name];
        }
        else
        {
            if(method_exists($this, 'create'.$name))
            {
                return $this->services[$name] = call_user_func(array($this, 'create'.$name));
            }
            else
            {
                throw new ServiceNotFoundException("Dependency injection container - Service $name not found");
            }
        }
    }


    /**
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function __call($name, array $args = array())
    {
        if(preg_match("~^get(?<name>[A-Z][a-zA-Z0-9_]*)~", $name, $match))
        {
            return $this->getService($match['name']);
        }

        throw new ServiceNotFoundException("Dependency injection container - Service $name not found");
    }
}
