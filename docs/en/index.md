# Quickstart

This extension integrates the [willdurand/geocoder](https://github.com/geocoder-php/Geocoder) into Nette Framework, which has [a lot of geocoding providers](https://github.com/geocoder-php/Geocoder#address-based-providers) implemented.

It also adds the `SeznamMaps` provider, and comparator logic for sorting the results and fiding the best match.


## Installation

You can install the extension using this command

```sh
$ composer require kdyby/geocoder:@dev
```

and enable the extension using your neon config.

```yml
extensions:
	geocoder: Kdyby\Geocoder\DI\GeocoderExtension
```


## Minimal configuration

The extension doesn't have a full support for configuring the providers graph, but for now, it at least configures the `httpAdapter`.

```yml
geocoder:
	httpAdapter: curl # default value is Ivory\HttpAdapter\CurlHttpAdapter
```

For that reason, you have to manually register the providers as services for now

```yml
services:
	- Geocoder\Provider\GoogleMaps(@geocoder.httpAdapter, cs_CZ, CZ, TRUE)
```

This should allow you to use the `GoogleMaps` geocoder as a service.


## Complete example

For my application, I had to use combination of providers, cache them and use comparators to get the result best matching the input.
The following example uses [Kdyby/Monolog](https://github.com/Kdyby/Monolog) and custom `CachingProvider`, which is not part of this package.

```yml
services:
	geocoder: CachingProvider(
		Kdyby\Geocoder\BestMatchAggregator(@geocoder.providers, Kdyby\Geocoder\Comparator\MoreData())
	)

	geocoder.providers:
		class: Geocoder\ProviderAggregator()
		setup:
			- registerProvider(@geocoder.seznam)
			- registerProvider(@geocoder.google)
		autowired: false

	geocoder.seznam:
		class: Kdyby\Geocoder\SilencingProvider(Kdyby\Geocoder\BestMatchProvider(
			Kdyby\Geocoder\Provider\SeznamMaps(@geocoder.httpAdapter),
			@geocoder.provider.comparator
		), @Kdyby\Monolog\Logger::channel(geocoder))
		setup:
			- limit(5)
		autowired: false

	geocoder.google:
		class: Kdyby\Geocoder\SilencingProvider(Kdyby\Geocoder\BestMatchProvider(
			Geocoder\Provider\GoogleMaps(@geocoder.httpAdapter, cs_CZ, CZ, TRUE),
			@geocoder.provider.comparator
		), @Kdyby\Monolog\Logger::channel(geocoder))
		setup:
			- limit(0)
		autowired: false

	geocoder.provider.comparator:
		class: Kdyby\Geocoder\Comparator\BigCitiesFirst(
			['Praha', 'Brno', 'Ostrava', 'Hradec Kr(á|a)lov(é|e)', 'Liberec', 'Plze(ň|n)', 'Olomouc'],
			Kdyby\Geocoder\Comparator\LevenshteinDistance()
		)
		autowired: false
```

First, we have two geocoding providers `geocoder.seznam` and `geocoder.google`. They're both wrapped in `BestMatchProvider`, which uses passed comparator to sort the results. Also, the `geocoder.seznam` has limit of 5 best results.

When the geocoding fails for some reason, the provider might throw an exception. But I don't wanna let it end, I want it to continue with the other provider. That's what the `SilencingProvider` is for - it catches the geocoder exceptions and converts them to logs using the Monolog.

Then we have a `geocoder.provider.comparator` which is a `BigCitiesFirst` comparator configured with list of sorted big cities from Czech Republic. This allows me to sort results based on the city the geocoder returns. Sometimes the user enters only the street and number without city. I'm simply assuming that when you enter only the street, you're from probably from Prague or Brno.

Next, there is a `LevenshteinDistance` comparator as a fallback for the `BigCitiesFirst`. When two entries are identical for the comparator, it uses the fallback and sorts them by levenshtein distance which, simply put, measures the number of different symbols in two strings. This is checked against the user input.

This is all wrapped in the `geocoder.providers`, which simple holds onto the providers.

The `geocoder.providers` is wrapped in `BestMatchAggregator` which iterates over all the providers, merges all the returned addresses and sorts them by the fact of simply having returned more data from the provider and if the data matches the user input better.

If you have this kind of complex setup, at the end you only have to call

```php
$results = $geocoder->geocode($searchQuery);
```

and you will have a `AddressCollection` of normalized addresses sorted by the similarity to the user input.
Calling the `$results->first()` returns the best matching address that you can then process.
