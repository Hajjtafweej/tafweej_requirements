<?php

return [
	'useOTL' => 0xFF,
	'useKashida' => 75,
	'font_path' => base_path('resources/assets/fonts/'),
	'font_data' => [
		'gess' => [
			'R'  => 'Gess-Medium.ttf',
			'B'  => 'Gess-Bold.ttf',
			'useOTL' => 0xFF,
			'useKashida' => 75
		],
		'frutiger' => [
			'R'  => 'Frutiger-LT-Arabic-65-Bold.ttf',
			'B'  => 'Frutiger-LT-Arabic-65-Bold.ttf',
			'useOTL' => 0xFF,
			'useKashida' => 75
		],
	],
	'mode'                  => 'utf-8',
	'format'                => 'A4',
	'author'                => '',
	'subject'               => '',
	'keywords'              => '',
	'creator'               => 'Laravel Pdf',
	'display_mode'          => 'fullpage',
	'tempDir'               => base_path('../temp/')
];
