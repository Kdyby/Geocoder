<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\Comparator;

use Geocoder\Model\Address;
use Kdyby;
use Kdyby\Geocoder\AddressComparator;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BigCitiesFirst extends Nette\Object implements AddressComparator
{

	/**
	 * @var array
	 */
	private $cityPriority;

	/**
	 * @var AddressComparator
	 */
	private $fallback;



	public function __construct(array $cityPriority, AddressComparator $fallback = NULL)
	{
		$this->cityPriority = array_flip($cityPriority);
		$this->fallback = $fallback;
	}



	/**
	 * {@inheritDoc}
	 */
	public function compare(Address $a, Address $b, $geocoderQuery)
	{
		$aContains = $this->queryContainsCity($a, $geocoderQuery);
		$bContains = $this->queryContainsCity($b, $geocoderQuery);

		if ($aContains && $bContains) {
			return $this->fallback ? $this->fallback->compare($a, $b, $geocoderQuery) : 0;

		} elseif ($aContains) {
			return -1;

		} elseif ($bContains) {
			return 1;
		}

		$aP = $bP = PHP_INT_MAX;

		foreach ($this->cityPriority as $city => $priority) {
			if (preg_match('~' . $city . '~i', trim($a->getLocality())) && $priority < $aP) {
				$aP = $priority;
			}

			if (preg_match('~' . $city . '~i', trim($b->getLocality())) && $priority < $bP) {
				$bP = $priority;
			}
		}

		if ($aP == $bP) {
			return $this->fallback ? $this->fallback->compare($a, $b, $geocoderQuery) : 0;
		}

		return $aP < $bP ? -1 : 1;
	}



	/**
	 * @param Address $address
	 * @param string $geocoderQuery
	 * @return boolean
	 */
	private function queryContainsCity(Address $address, $geocoderQuery)
	{
		return Strings::match($geocoderQuery, '~(\s|-|,|^)' . preg_quote($address->getLocality()) . '(\s|-|,|\z)~');
	}

}
