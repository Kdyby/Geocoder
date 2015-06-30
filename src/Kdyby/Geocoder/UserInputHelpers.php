<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder;

use Kdyby;
use Nette;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class UserInputHelpers extends Nette\Object
{

	/** regexes for parsing address */
	const RE_STREET = '(?P<street>(?:[0-9]+(?=[^/,]+))?[^/,0-9]+(?<![\s\,]))'; // '(?P<street>([0-9]+(?=[^/,]+))?[^/,0-9]+(?=[a-z\s\,]))';
	const RE_NUMBER = '(?P<number>[0-9]+(?:\/[0-9]+)?[a-z]?)';
	const RE_CITY = '(?P<city>(?:(?P<city_name>[^,-]+(?<!\s))(?:(?<!\s)-(?!\s)(?P<city_part>\\5[^,]+(?<!\s)))?)|(?:[^,]+(?<!\s)))';
	const RE_POSTAL_CODE = '(?P<psc>\d{3}\s?\d{2})';



	/**
	 * @param string $number
	 * @return ArrayHash|NULL
	 */
	public static function matchNumber($number)
	{
		if (!trim($number) || !$m = Strings::match(trim($number), '~' . self::RE_NUMBER . '~i')) {
			return NULL;
		}

		$m = self::normalizeNumber($m);

		return ArrayHash::from([
			'number' => !empty($m['number']) ? $m['number'] : NULL,
			'on' => !empty($m['on']) ? $m['on'] : NULL,
			'hn' => !empty($m['hn']) ? $m['hn'] : NULL,
		]);
	}



	public static function normalizeNumber($m)
	{
		if (empty($m)) {
			return $m;
		}

		if (!is_array($m)) {
			$m = self::matchNumber($m);
		}

		if (!empty($m['number'])) {
			if (strpos($m['number'], '/') !== FALSE) {
				list($m['hn'], $m['on']) = explode('/', $m['number'], 2);
			} elseif (is_numeric($m['number'])) {
				$m['hn'] = $m['number'];
			} else { // 3a
				$m['on'] = $m['number'];
			}
		}

		return $m;
	}



	/**
	 * @param string $street
	 * @return ArrayHash|NULL
	 */
	public static function matchStreet($street)
	{
		if (!$m = Strings::match(trim($street), '~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,.*)?~i')) {
			return NULL;
		}

		$m = self::normalizeNumber($m);

		return ArrayHash::from([
			'street' => $m['street'],
			'number' => !empty($m['number']) ? $m['number'] : NULL,
			'on' => !empty($m['on']) ? $m['on'] : NULL,
			'hn' => !empty($m['hn']) ? $m['hn'] : NULL,
		]);
	}



	/**
	 * @param string $postalCode
	 * @return ArrayHash|null
	 */
	public static function matchPostalCode($postalCode)
	{
		if (!$m = Strings::match($postalCode = trim($postalCode), '~^' . self::RE_POSTAL_CODE . '\s*' . str_replace('\\5', '\\3', self::RE_CITY) . '?\z~i')) {
			if (!$m = Strings::match($postalCode, '~^' . str_replace('\\5', '\\2', self::RE_CITY) . '?\s*' . self::RE_POSTAL_CODE . '\z~i')) {
				return NULL;
			}
		}

		return ArrayHash::from([
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			'postalCode' => Strings::replace(trim(!empty($m['postalCode']) ? $m['postalCode'] : NULL), '#\s#') ?: NULL,
			'country' => self::matchCountry($postalCode),
		]);
	}



	/**
	 * @param string $address
	 * @return ArrayHash|NULL
	 */
	public static function matchAddress($address)
	{
		if (!$m = Strings::match(trim($address), '~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,\s?' . self::RE_POSTAL_CODE . '|\,)(?:\s?' . self::RE_CITY . ')\,?~i')) {
			return NULL;
		}

		$m = self::normalizeNumber($m);

		return ArrayHash::from([
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			// 'cityPart' => !empty($m['city_part']) ? $m['city_part'] : NULL,
			'street' => !empty($m['street']) ? $m['street'] : NULL,
			'number' => !empty($m['number']) ? $m['number'] : NULL,
			'on' => !empty($m['on']) ? $m['on'] : NULL,
			'hn' => !empty($m['hn']) ? $m['hn'] : NULL,
			'postalCode' => Strings::replace(trim(!empty($m['postalCode']) ? $m['postalCode'] : NULL), '#\s#') ?: NULL,
			'country' => self::matchCountry($address),
		]);
	}



	/**
	 * @param string $address
	 * @return ArrayHash|NULL
	 */
	public static function matchFullAddress($address)
	{
		if (!$m = Strings::match(trim($address), '~^' . self::RE_STREET . '\s*' . self::RE_NUMBER . '?(?:\,\s?' . self::RE_POSTAL_CODE . '|\,)(?:\s?' . self::RE_CITY . ')\,~i')) {
			return NULL;
		}

		if (preg_match('~^[\d\s]{5,6}$~', $m['city']) && empty($m['postalCode'])) { // Brno 2, 602 00, Česká republika
			$m['postalCode'] = $m['city'];
			$m['city'] = $m['street'];
			unset($m['city_name']);
		}

		$m = self::normalizeNumber($m);

		return ArrayHash::from([
			'city' => !empty($m['city']) ? (!empty($m['city_name']) ? $m['city_name'] : $m['city']) : NULL,
			// 'cityPart' => !empty($m['city_part']) ? $m['city_part'] : NULL,
			'street' => !empty($m['street']) ? $m['street'] : NULL,
			'number' => !empty($m['number']) ? $m['number'] : NULL,
			'on' => !empty($m['on']) ? $m['on'] : NULL,
			'hn' => !empty($m['hn']) ? $m['hn'] : NULL,
			'postalCode' => Strings::replace(trim(!empty($m['postalCode']) ? $m['postalCode'] : NULL), '#\s#') ?: NULL,
			'country' => self::matchCountry($address),
		]);
	}



	/**
	 * @param string $address
	 * @return string|NULL
	 */
	public static function matchCountry($address)
	{
		if (!$m = Strings::match(trim($address), '~.*,\s*(?P<country>(?:Česká Republika|Slovenská Republika|Slovensko|Česko))$~i')) {
			return NULL;
		}

		return $m['country'];
	}

}
