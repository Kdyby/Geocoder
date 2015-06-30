<?php

/**
 * Test: Kdyby\Geocoder\SeznamMaps.
 *
 * @testCase KdybyTests\Geocoder\SeznamMapsProviderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Kdyby;
use Kdyby\Geocoder\Provider\SeznamMaps;
use Nette;
use Nette\Utils\Strings;
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
		$adapter = $this->mockAdapter();

		$provider = new SeznamMaps($adapter);
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
		$adapter = $this->mockAdapter();

		$provider = new SeznamMaps($adapter);
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
		$adapter = $this->mockAdapter();

		$provider = new SeznamMaps($adapter);
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



	public function dataGeocode_samples()
	{
		return array(
			array('Cejl 486/17, 60200 Brno, okres Brno-město', 'Cejl 17, Brno'),
			array('Černická 708/10, 30100 Plzeň, okres Plzeň-město', 'Černická 10, 30100 Plzeň'),
			array(' , 74245 Fulnek, okres Nový Jičín', 'Děrné'),
			array(' , 74245 Fulnek, okres Nový Jičín', 'Fulnek'),
			array('K Zelené louce 1484/2a, 14800 Praha, okres Hlavní město Praha', 'K Zelené louce 2a, Praha'),
			array('Ostrovského 365/7, 15000 Praha, okres Hlavní město Praha', 'Ostrovského 7, 150 00 Praha 5'),
			array(' , 69301 Starovičky, okres Břeclav', 'Starovičky'),
			array('tř. T. G. Masaryka 1119, 73801 Frýdek-Místek, okres Frýdek-Místek', 'T.G.Masaryka 1119, 73801 Frýdek-Místek'),
			array('Vaňkova , 19800 Praha, okres Hlavní město Praha', 'Vaňkova, Praha'),
			array('MCV Brno ,  Hrotovice, okres Třebíč', 'MCV Brno, Hrotovice'),
		);
	}



	/**
	 * @dataProvider dataGeocode_samples
	 */
	public function testGeocode_samples($expected, $input)
	{
		$adapter = $this->mockAdapter();

		$provider = new SeznamMaps($adapter);
		$addresses = $provider->limit(1)->geocode($input);

		Assert::count(1, $addresses);

		$address = $addresses->first();
		$adminLevel2 = $address->getAdminLevels()->has(2) ? $address->getAdminLevels()->get(2)->getName() : NULL;
		Assert::same($expected, sprintf('%s %s, %s %s, okres %s', $address->getStreetName(), $address->getStreetNumber(), $address->getPostalCode(), $address->getLocality(), $adminLevel2));
	}



	/**
	 * @return \Mockery\Mock|\Ivory\HttpAdapter\HttpAdapterInterface
	 */
	private function mockAdapter()
	{
		$adapter = \Mockery::mock('Ivory\HttpAdapter\HttpAdapterInterface')->shouldDeferMissing();
		$adapter->shouldReceive('get')->andReturnUsing(function ($url) {
			$url = new Nette\Http\Url($url);
			parse_str($url->getQuery(), $query);

			if ($url->path == '/rgeocode') {
				$target = str_replace('-', '_', Strings::webalize(sprintf('lon%s_lat%s', $query['lon'], $query['lat']), '._'));
				$targetFile = __DIR__ . '/SeznamMaps-data/rg_' . $target . '.xml';

			} elseif ($url->path == '/geocode') { // geocode
				$target = str_replace('-', '_', Strings::webalize($query['query'], '._'));
				$targetFile = __DIR__ . '/SeznamMaps-data/g_' . $target . '.xml';

			} else {
				throw new \LogicException(sprintf('Unexpected endpoint %s', $url->path));
			}

			if (!file_exists($targetFile)) {
				file_put_contents($targetFile, $body = file_get_contents((string) $url));
			} else {
				$body = file_get_contents($targetFile);
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
