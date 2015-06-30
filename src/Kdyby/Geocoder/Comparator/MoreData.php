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
		if (($compareCity = $this->compareCity($a, $b, $query)) !== 0) {
			return $compareCity;
		}

		if (($compareStreets = $this->compareStreet($a, $b, $query)) !== 0) {
			return $compareStreets;
		}

		if (($compareNumbers = $this->compareNumbers($a, $b, $query)) !== 0) {
			return $compareNumbers;
		}

		return $this->fallback ? $this->fallback->compare($a, $b, $query) : 0;
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
		// if one of the addresses doesn't even have a number in it,
		$aNumber = UserInputHelpers::normalizeNumber($a->getStreetNumber());
		$bNumber = UserInputHelpers::normalizeNumber($b->getStreetNumber());
		if ($aNumber && !$bNumber) {
			return -1;
		}
		if ($bNumber && !$aNumber) {
			return 1;
		}

		if (!$inputNumber = UserInputHelpers::matchStreet($query)) {
			return 0;
		}

		if ($this->isNumberFull($inputNumber)) { // preffer more exact
			$aEquals = $this->equalsNumber($aNumber, $inputNumber);
			$bEquals = $this->equalsNumber($bNumber, $inputNumber);
			if ($aEquals && !$bEquals) {
				return -1;
			}
			if ($bEquals && !$aEquals) {
				return 1;
			}
		}

		// preffer more data, but at least one component must equal
		$aPartially = $this->equalsNumberPartially($aNumber, $inputNumber);
		$bPartially = $this->equalsNumberPartially($bNumber, $inputNumber);
		if ($aPartially && !$bPartially) {
			return -1;
		}
		if ($bPartially && !$aPartially) {
			return 1;
		}

		$aHasMore = $this->isNumberFull($aNumber) && !$this->isNumberFull($bNumber);
		$bHasMore = $this->isNumberFull($bNumber) && !$this->isNumberFull($aNumber);
		if ($aHasMore && !$bHasMore) {
			return -1;
		}
		if ($bHasMore && !$aHasMore) {
			return 1;
		}

		return 0;
	}



	/**
	 * @param \stdClass $number
	 * @return bool
	 */
	protected function isNumberFull($number)
	{
		return !empty($number->hn) && !empty($number->on);
	}



	/**
	 * @param \stdClass $a
	 * @param \stdClass $b
	 * @return boolean
	 */
	protected function equalsNumber($a, $b)
	{
		if ($a->hn === $b->hn && strtolower($a->on) === strtolower($b->on)) {
			return TRUE;
		}

		if ($a->hn === strtolower($b->on) && strtolower($a->on) === $b->hn) {
			return TRUE;
		}

		return FALSE;
	}



	/**
	 * @param \stdClass $a
	 * @param \stdClass $b
	 * @return boolean
	 */
	protected function equalsNumberPartially($a, $b)
	{
		if (($b->hn !== NULL && $a->hn === $b->hn) || ($b->on !== NULL && strtolower($a->on) === strtolower($b->on))) {
			return TRUE;
		}

		if (($b->hn !== NULL && $a->hn === strtolower($b->on)) || ($b->hn !== NULL && strtolower($a->on) === $b->hn)) {
			return TRUE;
		}

		return FALSE;
	}



	/**
	 * @param \stdClass $a
	 * @param \stdClass $b
	 * @return boolean
	 */
	protected function equalsOrientationNumber($a, $b)
	{
		return strtolower($a->on) === strtolower($b->on);
	}



	/**
	 * @param \stdClass $a
	 * @param \stdClass $b
	 * @return boolean
	 */
	protected function equalsHouseNumber($a, $b)
	{
		return $a->hn === $b->hn;
	}

}
