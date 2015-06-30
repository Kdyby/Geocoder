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
use Kdyby\Geocoder\UserInputHelpers;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MoreData extends Nette\Object implements AddressComparator
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
	public function compare(Address $a, Address $b, $query)
	{
		if ($this->addressFullyMatchesInput($a, $query)) {
			return -1;

		} elseif ($this->addressFullyMatchesInput($b, $query)) {
			return 1;
		}

		if (($compareCity = $this->compareCity($a, $b, $query)) !== 0) {
			return $compareCity;
		}

		if (($compareStreets = $this->compareStreet($a, $b, $query)) !== 0) {
			return $compareStreets;
		}

		return $this->compareNumbers($a, $b, $query);
	}



	protected function addressFullyMatchesInput(Address $address, $query)
	{
		return $this->cityMatches($address, $query)
			&& $this->streetMatches($address, $query)
			&& $this->numberMatches($address, $query);
	}



	protected function cityMatches(Address $address, $query)
	{
		if (!$address->getLocality()) {
			return FALSE; // no city no fun
		}

		return stripos($query, $address->getLocality()) !== FALSE; // is there a city in the input?
	}



	protected function streetMatches(Address $address, $query)
	{
		if (!$address->getStreetName()) {
			return (bool) UserInputHelpers::matchAddress($query); // does the input look like it contains street?
		}

		return stripos($query, $address->getStreetName()) !== FALSE; // does the input contain the street?
	}



	protected function numberMatches(Address $address, $query)
	{
		$number = UserInputHelpers::normalizeNumber($address->getStreetNumber());

		if (!($m = UserInputHelpers::matchNumber($query))) { // if there is house number AND orientation number in input
			return FALSE;
		}

		if (!empty($m->hn)) {
			if (empty($number['hn']) || $m->hn !== $number['hn']) {
				return FALSE;
			}
		}

		if (!empty($m->on)) {
			if (empty($number['on']) || strtolower($m->on) !== strtolower($number['on'])) {
				return FALSE;
			}
		}

		return TRUE;
	}



	protected function compareCity(Address $a, Address $b, $query)
	{
		if (!$a->getLocality() && !$b->getLocality()) {
			return 0;

		} elseif ($a->getLocality() && !$b->getLocality()) {
			return -1;

		} elseif ($b->getLocality() && !$a->getLocality()) {
			return 1;
		}

		if (stripos($query, $a->getLocality()) !== FALSE && stripos($query, $a->getLocality()) === FALSE) {
			return -1;

		} elseif (stripos($query, $b->getLocality()) !== FALSE && stripos($query, $a->getLocality()) === FALSE) {
			return 1;
		}

		return 0;
	}



	protected function compareStreet(Address $a, Address $b, $query)
	{
		if (!$a->getStreetName() && !$b->getStreetName()) {
			return 0;

		} elseif ($a->getStreetName() && !$b->getStreetName()) {
			return -1;

		} elseif ($b->getStreetName() && !$a->getStreetName()) {
			return 1;
		}

		if (stripos($query, $a->getStreetName()) !== FALSE && stripos($query, $a->getStreetName()) === FALSE) {
			return -1;

		} elseif (stripos($query, $b->getStreetName()) !== FALSE && stripos($query, $a->getStreetName()) === FALSE) {
			return 1;
		}

		return 0;
	}



	protected function compareNumbers(Address $a, Address $b, $query)
	{
		if (!$inputNumber = UserInputHelpers::matchNumber($query)) {
			return $this->fallback ? $this->fallback->compare($a, $b, $query) : 0;
		}

		$aNumber = UserInputHelpers::normalizeNumber($a->getStreetNumber());
		$bNumber = UserInputHelpers::normalizeNumber($b->getStreetNumber());

		if ($aNumber && !$bNumber) {
			return -1;
		}

		if ($bNumber && !$aNumber) {
			return 1;
		}

		if ($inputNumber->hn) {
			if ($aNumber->hn !== $inputNumber->hn && $bNumber->hn === $inputNumber->hn) {
				return 1;
			} elseif ($aNumber->hn === $inputNumber->hn && $bNumber->hn !== $inputNumber->hn) {
				return -1;
			}
		}

		if ($inputNumber->on) {
			if (strtolower($aNumber->on) !== strtolower($inputNumber->on) && strtolower($bNumber->on) === strtolower($inputNumber->on)) {
				return 1;
			} elseif (strtolower($aNumber->on) === strtolower($inputNumber->on) && strtolower($bNumber->on) !== strtolower($inputNumber->on)) {
				return -1;
			}
		}

		return $this->fallback ? $this->fallback->compare($a, $b, $query) : 0;
	}

}
