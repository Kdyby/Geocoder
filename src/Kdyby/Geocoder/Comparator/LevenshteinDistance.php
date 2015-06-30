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



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LevenshteinDistance extends Nette\Object implements AddressComparator
{

	/**
	 * @var AddressComparator
	 */
	private $fallback;



	public function __construct(AddressComparator $fallback = NULL)
	{
		$this->fallback = $fallback;
	}



	/**
	 * {@inheritDoc}
	 */
	public function compare(Address $a, Address $b, $geocoderQuery)
	{
		$aL = (int) levenshtein($this->formatSimpleFull($a), $geocoderQuery);
		$bL = (int) levenshtein($this->formatSimpleFull($b), $geocoderQuery);

		if ($aL == $bL) {
			return $this->fallback ? $this->fallback->compare($a, $b, $geocoderQuery) : 0;
		}

		return $aL > $bL ? 1 : -1;
	}



	private function formatSimpleFull(Address $address)
	{
		return ($address->getStreetName() ? $this->formatStreet($address) . ', ' : '') . $address->getLocality();
	}



	private function formatStreet(Address $address)
	{
		return $address->getStreetName() . ($address->getStreetNumber() ? ' ' . $address->getStreetNumber() : '');
	}

}
