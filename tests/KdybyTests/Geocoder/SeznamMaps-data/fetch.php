<?php

$rgeocode = [
	'rg_lon14_lat50' => ['lon' => '14.417900', 'lat' => '50.126550'],
	'rg_lon16_lat49' => ['lon' => '16.604951', 'lat' => '49.18817'],
];

foreach ($rgeocode as $target => $query) {
	if (file_exists($targetFile = __DIR__ . '/' . $target . '.xml')) {
		continue;
	}

	file_put_contents($targetFile, file_get_contents('https://api4.mapy.cz/rgeocode?' . http_build_query($query, NULL, '&')));
}

$geocode = [ // https://api4.mapy.cz/geocode
	'g_soukenicka_5_brno' => ['query' => 'Soukenická 5, Brno'],
	'g_soukenicka_5' => ['query' => 'soukenická 5'],
	'g_brno_stred' => ['query' => 'brno střed'],
	'g_jihomoravsky_kraj' => ['query' => 'jihomoravský kraj'],
	'g_okres_znojmo' => ['query' => 'okres Znojmo'],
	'g_brno' => ['query' => 'brno'],
	'g_ceska_republika' => ['query' => 'česká republika'],
];

foreach ($geocode as $target => $query) {
	if (file_exists($targetFile = __DIR__ . '/' . $target . '.xml')) {
		continue;
	}

	file_put_contents($targetFile, file_get_contents('https://api4.mapy.cz/geocode?' . http_build_query($query, NULL, '&')));
}
