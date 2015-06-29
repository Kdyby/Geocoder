<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Geocoder\DI;

use Kdyby;
use Nette;
use Nette\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class GeocoderExtension extends Nette\DI\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'providers' => array(),
		'httpAdapter' => 'curl'
	);

	/**
	 * @var array
	 */
	public static $providers = array(
		'Kdyby\Geocoder\Provider\SeznamMapsProvider',
		'Geocoder\Provider\ArcGISOnline',
		'Geocoder\Provider\BingMaps',
		'Geocoder\Provider\FreeGeoIp',
		'Geocoder\Provider\Geoip',
		'Geocoder\Provider\GeoIP2',
		'Geocoder\Provider\GeoIPs',
		'Geocoder\Provider\Geonames',
		'Geocoder\Provider\GeoPlugin',
		'Geocoder\Provider\GoogleMaps',
		'Geocoder\Provider\GoogleMapsBusiness',
		'Geocoder\Provider\HostIp',
		'Geocoder\Provider\IpInfoDb',
		'Geocoder\Provider\MapQuest',
		'Geocoder\Provider\MaxMind',
		'Geocoder\Provider\MaxMindBinary',
		'Geocoder\Provider\Nominatim',
		'Geocoder\Provider\OpenCage',
		'Geocoder\Provider\OpenStreetMap',
		'Geocoder\Provider\TomTom',
		'Geocoder\Provider\Yandex',
	);

	public static $httpAdapters = array(
		'curl' => 'Ivory\HttpAdapter\CurlHttpAdapter',
	);



	public function __construct()
	{
		$providers = self::$providers;
		self::$providers = array();

		foreach ($providers as $providerClass) {
			if (!class_exists($providerClass)) {
				continue;
			}

			$refl = new \ReflectionClass($providerClass);
			$providerMock = $refl->newInstanceWithoutConstructor();
			self::$providers[$providerMock->getName()] = $providerClass;
		}
	}



	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);

		$this->loadHttpAdapter($config);
		$this->loadProviders($config);
	}



	protected function loadHttpAdapter(array $config)
	{
		$httpAdapter = self::filterArgs($config['httpAdapter'])[0];
		if (isset(self::$httpAdapters[$httpAdapter->entity])) {
			$httpAdapter->entity = self::$httpAdapters[$httpAdapter->entity];
		}

		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('httpAdapter'))
			->setFactory($httpAdapter->entity, $httpAdapter->arguments)
			->setAutowired(FALSE);
	}



	protected function loadProviders(array $config)
	{
		$builder = $this->getContainerBuilder();
		$aggregator = $builder->addDefinition($this->prefix('aggregator'))
			->setClass('Geocoder\ProviderAggregator');

		$i = 0;
		foreach ($config['providers'] as $provider) {
			$provider = self::filterArgs($provider)[0];

			if (isset(self::$providers[$provider->entity])) { // alias
				$provider->entity = self::$providers[$provider->entity];
			}

			if (empty($provider->arguments[0]) || $provider->arguments[0] === '...') {
				$providerRefl = new \ReflectionClass($provider->entity);
				$constructorRefl = $providerRefl->getConstructor();
				$parametersRefl = $constructorRefl->getParameters();
				if (isset($parametersRefl[0]) && ($paramClass = $parametersRefl[0]->getClass()) && $paramClass->getName() === 'Ivory\HttpAdapter\HttpAdapterInterface') {
					$provider->arguments[0] = $this->prefix('@httpAdapter');
				}
			}

			$providerService = $builder->addDefinition($this->prefix('provider.' . ($i++)))
				->setFactory($provider->entity, $provider->arguments);
			$aggregator->addSetup('registerProvider', [$providerService]);
		}
	}



	/**
	 * @param string|\stdClass $statement
	 * @return Nette\DI\Statement[]
	 */
	private function filterArgs($statement)
	{
		return Nette\DI\Compiler::filterArguments(array(is_string($statement) ? new Nette\DI\Statement($statement) : $statement));
	}



	/**
	 * @param \Nette\Configurator $configurator
	 */
	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('geocoder', new GeocoderExtension());
		};
	}

}
