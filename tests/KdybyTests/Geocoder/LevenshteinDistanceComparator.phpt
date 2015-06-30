<?php

/**
 * Test: Kdyby\Geocoder\LevenshteinDistanceComparator.
 *
 * @testCase KdybyTests\Geocoder\LevenshteinDistanceComparatorTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Geocoder
 */

namespace KdybyTests\Geocoder;

use Geocoder\Model\Address;
use Kdyby;
use Kdyby\Geocoder\Comparator\LevenshteinDistance;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LevenshteinDistanceComparatorTest extends Tester\TestCase
{

	/**
	 * @dataProvider dataCompare
	 */
	public function testCompare($expected, $query, Address $a, Address $b)
	{
		$comparator = new LevenshteinDistance();
		Assert::same($expected, $comparator->compare($a, $b, $query));
	}



	public function dataCompare()
	{
		return [
			[
				1, 'Soukenická 5',
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5)
			],
			[
				-1, 'Soukenická 5',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5)
			],
			[
				0, 'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5)
			],
			[
				1, 'Soukenická 5, Brno',
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5)
			],
			[
				-1, 'Soukenická 5, Brno',
				Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
				Helpers::createAddress('Praha', 'Soukenická', NULL, 5)
			],
			[
				1, 'Soukenická 5',
				Helpers::createAddress('Hradec Králové', 'Soukenická', NULL, 5),
				Helpers::createAddress('Plzeň', 'Soukenická', NULL, 5)
			],
			[
				1, 'Soukenická 5',
				Helpers::createAddress('Hradec Kralove', 'Soukenická', NULL, 5),
				Helpers::createAddress('Plzeň', 'Soukenická', NULL, 5)
			],
		];
	}



	public function testFunctional()
	{
		$query = 'Soukenická 5';
		$list = [
			$b2 = Helpers::createAddress('Brno', 'Soukenicka', NULL, 5),
			$b1 = Helpers::createAddress('Brno', 'Soukenická', NULL, 5),
			$a = Helpers::createAddress('Praha', 'Soukenická', NULL, 5),
			$g = Helpers::createAddress('Olomouc', 'Soukenická', NULL, 5),
			$e = Helpers::createAddress('Liberec', 'Soukenická', NULL, 5),
			$c = Helpers::createAddress('Ostrava', 'Soukenická', NULL, 5),
		];

		$comparator = new LevenshteinDistance();
		@usort($list, function (Address $a, Address $b) use ($comparator, $query) {
			return $comparator->compare($a, $b, $query);
		});

		Assert::same([$b1, $a, $b2, $c, $g, $e], $list);
	}

}

\run(new LevenshteinDistanceComparatorTest());
