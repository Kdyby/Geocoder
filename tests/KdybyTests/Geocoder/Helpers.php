<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace KdybyTests\Geocoder;

use Geocoder\Model\Address;
use Geocoder\Model\AddressFactory;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Helpers extends Nette\Object
{

	/**
	 * @return Address
	 */
	public static function createAddress($city, $street, $houseNumber = NULL, $orientationNumber = NULL, $postalCode = NULL)
	{
		$factory = new AddressFactory();
		return $factory->createFromArray([
			[
				'locality' => $city,
				'streetName' => $street,
				'streetNumber' => implode('/', array_filter([$houseNumber, $orientationNumber])),
				'postalCode' => $postalCode,
			]
		])->first();
	}

}
