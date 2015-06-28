<?php

/**
 * Test: Kdyby\Geocoder\SeznamMapsProvider.
 *
 * @testCase KdybyTests\Geocoder\SeznamMapsProviderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Kdyby;
use Kdyby\Geocoder\Provider\SeznamMapsProvider;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SeznamMapsProviderTest extends Tester\TestCase
{

	public function testReverse_addr()
	{
		$adapter = $this->mockAdapter(array(
			file_get_contents(__DIR__ . '/SeznamMaps-data/rg_lon16.604951_lat49.18817.xml'),
		));

		$provider = new SeznamMapsProvider($adapter);
		$addresses = $provider->reverse('49.188170408', '16.6049509394');

		Assert::count(1, $addresses);

		$address = $addresses->first();
		Assert::same(49.188170408, $address->getLatitude());
		Assert::same(16.6049509394, $address->getLongitude());
		Assert::same('Česká republika', $address->getCountry()->getName());
		Assert::null($address->getCountry()->getCode());
		Assert::same('60200', $address->getPostalCode());
		Assert::same('Brno', $address->getLocality());
		Assert::same('Soukenická', $address->getStreetName());
		Assert::same('559/5', $address->getStreetNumber());

		$adminLevels = $address->getAdminLevels();
		Assert::count(4, $adminLevels);
		Assert::same('Brno-střed', $adminLevels->get(4)->getName());
		Assert::same('Staré Brno', $adminLevels->get(3)->getName());
		Assert::same('Brno-město', $adminLevels->get(2)->getName());
		Assert::same('Jihomoravský', $adminLevels->get(1)->getName());
	}



	public function testGeocode_addr()
	{
		$adapter = $this->mockAdapter(array(
			file_get_contents(__DIR__ . '/SeznamMaps-data/g_soukenicka_5_brno.xml'),
			file_get_contents(__DIR__ . '/SeznamMaps-data/rg_lon16.604951_lat49.18817.xml'),
		));

		$provider = new SeznamMapsProvider($adapter);
		$addresses = $provider->geocode("Soukenická 5, Brno");

		Assert::count(1, $addresses);

		$address = $addresses->first();
		Assert::same(49.188170408, $address->getLatitude());
		Assert::same(16.6049509394, $address->getLongitude());
		Assert::same('Česká republika', $address->getCountry()->getName());
		Assert::null($address->getCountry()->getCode());
		Assert::same('60200', $address->getPostalCode());
		Assert::same('Brno', $address->getLocality());
		Assert::same('Soukenická', $address->getStreetName());
		Assert::same('559/5', $address->getStreetNumber());

		$adminLevels = $address->getAdminLevels();
		Assert::count(4, $adminLevels);
		Assert::same('Brno-střed', $adminLevels->get(4)->getName());
		Assert::same('Staré Brno', $adminLevels->get(3)->getName());
		Assert::same('Brno-město', $adminLevels->get(2)->getName());
		Assert::same('Jihomoravský', $adminLevels->get(1)->getName());
	}



	public function testGeocode_stre()
	{
		$adapter = $this->mockAdapter(array(
			file_get_contents(__DIR__ . '/SeznamMaps-data/g_soukenicka_brno.xml'),
			file_get_contents(__DIR__ . '/SeznamMaps-data/rg_lon16.605550766_lat49.1882400513.xml'),
		));

		$provider = new SeznamMapsProvider($adapter);
		$addresses = $provider->geocode("Soukenická, Brno");

		Assert::count(1, $addresses);

		$address = $addresses->first();
		Assert::same(49.1882410227, $address->getLatitude());
		Assert::same(16.6055516684, $address->getLongitude());
		Assert::same('Česká republika', $address->getCountry()->getName());
		Assert::null($address->getCountry()->getCode());
		Assert::null($address->getPostalCode());
		Assert::same('Brno', $address->getLocality());
		Assert::same('Soukenická', $address->getStreetName());
		Assert::null($address->getStreetNumber());

		$adminLevels = $address->getAdminLevels();
		Assert::count(4, $adminLevels);
		Assert::same('Brno-střed', $adminLevels->get(4)->getName());
		Assert::same('Staré Brno', $adminLevels->get(3)->getName());
		Assert::same('Brno-město', $adminLevels->get(2)->getName());
		Assert::same('Jihomoravský', $adminLevels->get(1)->getName());
	}



	/**
	 * @return \Mockery\Mock|\Ivory\HttpAdapter\HttpAdapterInterface
	 */
	private function mockAdapter(array $responses = array())
	{
		$adapter = \Mockery::mock('Ivory\HttpAdapter\HttpAdapterInterface')->shouldDeferMissing();
		$adapter->shouldReceive('get')->andReturnUsing(function ($url) use (&$responses) {
			if (!$body = array_shift($responses)) {
				throw new \LogicException("Missing response for $url");
			}

			$response = \Mockery::mock('\Ivory\HttpAdapter\Message\ResponseInterface')->shouldDeferMissing();
			$response->shouldReceive('getBody')->andReturn($body);

			return $response;
		});

		return $adapter;
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

}

\run(new SeznamMapsProviderTest());
