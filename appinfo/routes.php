<?php

return [
	'routes' => [
		[
			'name' => 'devices#index',
			'url' => '/devices',
			'verb' => 'GET',
		],
		[
			'name' => 'devices#setDeviceState',
			'url'  => '/devices/{id}',
			'verb' => 'PUT'
		],
		[
			'name' => 'devices#getDeviceIcon',
			'url' => '/devices/icon/{id}',
			'verb' => 'GET',
		],
		[
			'name' => 'devices#dashboard',
			'url' => '/dashboard',
			'verb' => 'GET',
		],
		[
			'name' => 'settings#setConfig',
			'url' => '/settings',
			'verb' => 'PUT'
		]
	]
];
