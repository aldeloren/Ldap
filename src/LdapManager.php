<?php namespace Xavrsl\Ldap;

use Illuminate\Support\Manager;

class LdapManager {

	/**
	 * The active connection instances.
	 *
	 * @var array
	 */
	protected $connections = array();

	/**
	 * Get a Ldap connection instance.
	 *
	 * @param  string  $name
	 * @return Xavrsl\Ldap\Directory
	 */
	public function connection($name = null)
	{
		if ( ! isset($this->connections[$name]))
		{
			$this->connections[$name] = $this->createConnection($name);
		}

		return $this->connections[$name];
	}

	/**
	 * Create the given connection by name.
	 *
	 * @param  string  $name
	 * @return Xavrsl\Ldap\Directory
	 */
	protected function createConnection($name)
	{
		$config = $this->getConfig($name);

		$connection = new Directory($config, new Connection($config));

		return $connection;
	}

	/**
	 * Get the configuration for a connection.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		$name = $name ?: $this->getDefaultConnection();

		// To get the database connection configuration, we will just pull each of the
		// connection configurations and get the configurations for the given name.
		// If the configuration doesn't exist, we'll throw an exception and bail.
		// $connections = $this->app['config']['database.ldap'];
		$connections = \Config::get('ldap::'.$name);

		if (is_null($connections))
		{
			throw new \InvalidArgumentException("Ldap [$name] not configured.");
		}

		return $connections;
	}

	/**
	 * Get the default connection name.
	 *
	 * @return string
	 */
	protected function getDefaultConnection()
	{
		return 'default';
	}

	/**
	 * Dynamically pass methods to the default connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->connection(), 'query'), array($method, $parameters));
	}

}
