<?php

/**
 * Test: Kdyby\Geocoder\ProxiedGoogleMaps.
 *
 * @testCase KdybyTests\Geocoder\ProxiedGoogleMapsTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Kdyby;
use Nette;
use Nette\Utils\Strings;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ProxiedGoogleMapsTest extends Tester\TestCase
{

	public function testGeocode()
	{
		/** @var \Mockery\Mock|\Ivory\HttpAdapter\HttpAdapterInterface $adapter */
		$adapter = \Mockery::mock('Ivory\HttpAdapter\HttpAdapterInterface')->shouldDeferMissing();
		$adapter->shouldReceive('get')->once()->andReturnUsing(function () {
			$response = \Mockery::mock('Ivory\HttpAdapter\Message\ResponseInterface')->shouldDeferMissing();
			$response->shouldReceive('getBody')->andReturn(file_get_contents(__DIR__ . '/GoogleMaps-data/soukenicka_5_brno.json'));
			return $response;
		})->withArgs(['https://geocoder.kdyby.org/?address=Soukenick%C3%A1%205%2C%20Brno&language=cs_CZ&region=CZ&key=nemam']);

		$provider = new Kdyby\Geocoder\Provider\ProxiedGoogleMaps($adapter, 'https://geocoder.kdyby.org/', 'cs_CZ', 'CZ', 'nemam');
		$result = $provider->geocode('Soukenická 5, Brno');

		$address = $result->first();
		Assert::same(49.1881705, $address->getLatitude());
		Assert::same(16.6049561, $address->getLongitude());
	}

}

\run(new ProxiedGoogleMapsTest());
