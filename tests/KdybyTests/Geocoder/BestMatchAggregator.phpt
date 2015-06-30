<?php

/**
 * Test: Kdyby\Geocoder\BestMatchAggregator.
 *
 * @testCase KdybyTests\Geocoder\BestMatchAggregatorTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Geocoder\Model\AddressCollection;
use Geocoder\ProviderAggregator;
use Kdyby;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BestMatchAggregatorTest extends Tester\TestCase
{

	public function testGeocoder()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5, 559);
		$b = Helpers::createAddress('Brno', 'Soukenická', 5);

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider1 */
		$provider1 = \Mockery::mock('Geocoder\Provider\Provider');
		$provider1->shouldReceive('geocode')->andReturn(new AddressCollection([$a]));

		/** @var \Geocoder\Provider\Provider|\Mockery\MockInterface $provider1 */
		$provider2 = \Mockery::mock('Geocoder\Provider\Provider');
		$provider2->shouldReceive('geocode')->andReturn(new AddressCollection([$b]));

		/** @var ProviderAggregator|\Mockery\MockInterface $aggregator */
		$aggregator = \Mockery::mock('Geocoder\ProviderAggregator');
		$aggregator->shouldReceive('getLimit')->andReturn(5);
		$aggregator->shouldReceive('getProviders')->andReturn([
			$provider1,
			$provider2
		]);

		/** @var Kdyby\Geocoder\AddressComparator|\Mockery\MockInterface $comparator */
		$comparator = \Mockery::mock('Kdyby\Geocoder\AddressComparator');
		$comparator->shouldReceive('compare')->andReturn(1);

		$geocoder = new Kdyby\Geocoder\BestMatchAggregator($aggregator, $comparator);
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

\run(new BestMatchAggregatorTest());
