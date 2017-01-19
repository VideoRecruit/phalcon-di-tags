<?php

namespace VideoRecruit\Phalcon\DI;

use Closure;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\ServiceInterface;
use Phalcon\DiInterface;

/**
 * Class Container
 *
 * @package VideoRecruit\Phalcon\DI
 *
 * @method ServiceInterface attempt(string $name, mixed $definition, bool $shared = FALSE)
 * @method mixed get(string $name, array $parameters = NULL)
 * @method mixed getRaw(string $name)
 * @method ServiceInterface getService(string $name)
 * @method ServiceInterface[] getServices()
 * @method mixed getShared(string $name, array $parameters = NULL)
 * @method bool has(string $name)
 * @method void remove(string $name)
 * @method bool wasFreshInstance()
 */
class Container
{

	/**
	 * @var DiInterface
	 */
	private $di;

	/**
	 * @var array
	 */
	private $tagMap = [];

	/**
	 * Container constructor.
	 *
	 * @param DiInterface $di
	 */
	public function __construct(DiInterface $di = NULL)
	{
		$this->di = $di ?: new FactoryDefault;
	}

	/**
	 * @param string $name
	 * @param mixed $definition
	 * @param bool $shared
	 * @param string|NULL $tag
	 * @return ServiceInterface
	 */
	public function set($name, $definition, $shared = FALSE, $tag = NULL)
	{
		if ($this->isClosure($definition)) {
			$definition = Closure::bind($definition, $this->di);
		}

		$service = $this->di->set($name, $definition, $shared);

		if ($tag) {
			$this->addTag($name, $tag);
		}

		return $service;
	}

	/**
	 * @param string $name
	 * @param mixed $definition
	 * @param string|NULL $tag
	 * @return ServiceInterface
	 */
	public function setShared($name, $definition, $tag = NULL)
	{
		if ($this->isClosure($definition)) {
			$definition = Closure::bind($definition, $this->di);
		}

		$service = $this->di->setShared($name, $definition);

		if ($tag) {
			$this->addTag($name, $tag);
		}

		return $service;
	}

	/**
	 * @param string $name
	 * @param ServiceInterface $definition
	 * @param string|NULL $tag
	 * @return ServiceInterface
	 */
	public function setRaw($name, ServiceInterface $definition, $tag = NULL)
	{
		$service = $this->di->setRaw($name, $definition);

		if ($tag) {
			$this->addTag($name, $tag);
		}

		return $service;
	}

	/**
	 * @param string $tag
	 * @return ServiceInterface[]
	 * @throws InvalidArgumentException
	 */
	public function getServicesByTag($tag)
	{
		if (!is_string($tag)) {
			throw new InvalidArgumentException('Tag has to be a string.');
		}

		$services = [];

		if (array_key_exists($tag, $this->tagMap)) {
			foreach ($this->tagMap[$tag] as $serviceName) {
				$services[] = $this->di->getService($serviceName);
			}
		}

		return $services;
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if (!method_exists($this, $name)) {
			return $this->di->{$name}(...$arguments);
		}
	}

	/**
	 * @param string $serviceName
	 * @param string $tag
	 * @return self
	 */
	private function addTag($serviceName, $tag)
	{
		$tags = [];

		if (array_key_exists($tag, $this->tagMap)) {
			$tags = $this->tagMap[$tag];
		}

		$tags[] = $serviceName;
		$this->tagMap[$tag] = array_unique($tags);

		return $this;
	}

	/**
	 * @param mixed $definition
	 * @return bool
	 */
	private function isClosure($definition)
	{
		return is_object($definition) && $definition instanceof Closure;
	}
}
