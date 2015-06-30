<?php

/**
 * Test: Kdyby\Geocoder\SilencingProvider.
 *
 * @testCase KdybyTests\Geocoder\SilencingProviderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Model\AddressCollection;
use Kdyby;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SilencingProviderTest extends Tester\TestCase
{

	public function testGeocode()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5);

		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andReturn(new AddressCollection([$a]));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		$result = $provider->geocode('Soukenická 5');

		Assert::same($a, $result->first());
	}



	public function testGeocode_noResult()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new NoResult('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}



	public function testGeocode_quotaExceeded()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new QuotaExceeded('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->withArgs(['QuotaExceeded(inner): message']);

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}



	public function testGeocode_exception()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new InvalidArgument('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->withArgs(['Geocoder\Exception\InvalidArgument(inner): message']);

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}



	public function testReverse()
	{
		$a = Helpers::createAddress('Brno', 'Soukenická', 5);

		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andReturn(new AddressCollection([$a]));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		$result = $provider->reverse(49.1881713867, 16.6049518585);

		Assert::same($a, $result->first());
	}



	public function testReverse_noResult()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new NoResult('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}



	public function testReverse_quotaExceeded()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new QuotaExceeded('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->withArgs(['QuotaExceeded(inner): message']);

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}



	public function testReverse_exception()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new InvalidArgument('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->withArgs(['Geocoder\Exception\InvalidArgument(inner): message']);

		$provider = new Kdyby\Geocoder\SilencingProvider($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}



	protected function tearDown()
	{
		\Mockery::close();
	}



	/**
	 * @return \Geocoder\Provider\Provider|\Mockery\MockInterface
	 */
	private function mockProvider()
	{
		return \Mockery::mock('Geocoder\Provider\Provider');
	}



	/**
	 * @return \Psr\Log\LoggerInterface|\Mockery\MockInterface
	 */
	private function mockLogger()
	{
		return \Mockery::mock('Psr\Log\LoggerInterface');
	}

}

\run(new SilencingProviderTest());
