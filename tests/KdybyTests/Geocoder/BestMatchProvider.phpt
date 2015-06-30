<?php

/**
 * Test: Kdyby\Geocoder\BestMatchProvider.
 *
 * @testCase KdybyTests\Geocoder\BestMatchProviderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Geocoder\Model\AddressCollection;
use Kdyby;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BestMatchProviderTest extends Tester\TestCase
{

	public function testGeocoder()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5, 559);
		$b = Helpers::createAddress('Brno', 'Soukenická', 5);

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider */
		$provider = \Mockery::mock('Geocoder\Provider\Provider');
		$provider->shouldReceive('geocode')->andReturn(new AddressCollection([$a, $b]));
		$provider->shouldReceive('limit')->andReturn($provider);

		/** @var Kdyby\Geocoder\AddressComparator|\Mockery\MockInterface $comparator */
		$comparator = \Mockery::mock('Kdyby\Geocoder\AddressComparator');
		$comparator->shouldReceive('compare')->andReturn(1);

		$geocoder = new Kdyby\Geocoder\BestMatchProvider($provider, $comparator);
		$result = $geocoder->geocode('Soukenická 5, Brno');

		Assert::same($a, $result->first());
		Assert::same($a, $result->get(0));
		Assert::same($b, $result->get(1));
	}



	protected function tearDown()
	{
		\Mockery::close();
	}

}

\run(new BestMatchProviderTest());
