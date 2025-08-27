<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Helpers\Common\GeoIP\Drivers;

use App\Helpers\Common\GeoIP\AbstractDriver;
use Illuminate\Support\Facades\Http;
use Throwable;

class Ipinfo extends AbstractDriver
{
	public function get($ip)
	{
		$data = $this->getRaw($ip);
		
		if (empty($data) || is_string($data) || data_get($data, 'bogon')) {
			return $this->getDefault($ip, $data);
		}
		
		$loc = data_get($data, 'loc');
		$locArray = !empty($loc) ? explode(',', $loc) : [];
		
		return [
			'driver'      => config('geoip.default'),
			'ip'          => $ip,
			'city'        => data_get($data, 'city'),
			'country'     => null,
			'countryCode' => data_get($data, 'country'),
			'latitude'    => $locArray[0] ?? null,
			'longitude'   => $locArray[1] ?? null,
			'region'      => data_get($data, 'region'),
			'regionCode'  => null,
			'timezone'    => data_get($data, 'timezone'),
			'postalCode'  => data_get($data, 'postal'),
		];
	}
	
	/**
	 * ipinfo
	 * https://ipinfo.io/
	 * Free plan: Geolocation: 50k requests per month
	 *
	 * @param $ip
	 * @return array|mixed|string
	 */
	public function getRaw($ip)
	{
		$token = config('geoip.drivers.ipinfo.token');
		
		$url = 'https://ipinfo.io/' . $ip . '/json?token=' . $token;
		
		try {
			$response = Http::get($url);
			if ($response->successful()) {
				return $response->json();
			}
		} catch (Throwable $e) {
			$response = $e;
		}
		
		return parseHttpRequestError($response);
	}
}
