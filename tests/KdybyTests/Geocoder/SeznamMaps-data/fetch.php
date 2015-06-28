<?php

use Nette\Utils\Strings;



if (@!include __DIR__ . '/../../../../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

$rgeocode = [
	['lon' => '14.417900', 'lat' => '50.126550'],
	['lon' => '16.604951', 'lat' => '49.18817'],
	['lon' => '16.605550766', 'lat' => '49.1882400513'],
];

foreach ($rgeocode as $query) {
	$target = str_replace('-', '_', Strings::webalize(sprintf('lon%s_lat%s', $query['lon'], $query['lat']), '._'));
	if (file_exists($targetFile = __DIR__ . '/rg_' . $target . '.xml')) {
		continue;
	}

	file_put_contents($targetFile, file_get_contents('https://api4.mapy.cz/rgeocode?' . http_build_query($query, NULL, '&')));
}

$geocode = [ // https://api4.mapy.cz/geocode
	['query' => 'Soukenická 5, Brno'],
	['query' => 'Soukenická 5'],
	['query' => 'Soukenická, Brno'],
	['query' => 'Soukenická'],
	['query' => 'brno střed'],
	['query' => 'jihomoravský kraj'],
	['query' => 'okres Znojmo'],
	['query' => 'brno'],
	['query' => 'česká republika'],
];

foreach ($geocode as $query) {
	$target = str_replace('-', '_', Strings::webalize($query['query'], '._'));
	if (file_exists($targetFile = __DIR__ . '/g_' . $target . '.xml')) {
		continue;
	}

	file_put_contents($targetFile, file_get_contents('https://api4.mapy.cz/geocode?' . http_build_query($query, NULL, '&')));
}
