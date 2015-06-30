<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder;

use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\AbstractProvider;
use Geocoder\Provider\Provider;
use Geocoder\ProviderAggregator;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BestMatchAggregator extends AbstractProvider implements Provider
{

	/**
	 * @var ProviderAggregator
	 */
	private $aggregator;

	/**
	 * @var AddressComparator
	 */
	private $comparator;



	public function __construct(ProviderAggregator $aggregator, AddressComparator $comparator)
	{
		parent::__construct();
		$this->aggregator = $aggregator;
		$this->comparator = $comparator;
	}



	/**
	 * {@inheritDoc}
	 */
	public function geocode($value)
	{
		$allAddresses = [];
		foreach ($this->aggregator->getProviders() as $provider) {
			$allAddresses = array_merge($allAddresses, $provider->geocode($value)->all());
		}

		@uasort($allAddresses, function (Address $a, Address $b) use ($value) {
			return $this->comparator->compare($a, $b, $value);
		}); // intentionally, usorts cries when compared objects are modified

		return new AddressCollection(array_slice($allAddresses, 0, $this->getLimit()));
	}



	/**
	 * {@inheritDoc}
	 */
	public function reverse($latitude, $longitude)
	{
		$reversed = $this->aggregator->reverse($latitude, $longitude);
		return new AddressCollection($reversed->slice(0, $this->getLimit()));
	}



	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'best_match';
	}

}
