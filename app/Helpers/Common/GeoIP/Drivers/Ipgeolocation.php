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

class Ipgeolocation extends AbstractDriver
{
	public function get($ip)
	{
		$data = $this->getRaw($ip);
		
		if (empty($data) || is_string($data)) {
			return $this->getDefault($ip, $data);
		}
		
		return [
			'driver'      => config('geoip.default'),
			'ip'          => $ip,
			'city'        => data_get($data, 'city'),
			'country'     => data_get($data, 'country_name'),
			'countryCode' => data_get($data, 'country_code2'),
			'latitude'    => (float)number_format(data_get($data, 'latitude'), 5),
			'longitude'   => (float)number_format(data_get($data, 'longitude'), 5),
			'region'      => data_get($data, 'state_prov'),
			'regionCode'  => null,
			'timezone'    => data_get($data, 'time_zone.name'),
			'postalCode'  => data_get($data, 'zipcode'),
		];
	}
	
	/**
	 * ipgeolocation
	 * https://ipgeolocation.io/
	 * Free Plan: 30,000 requests / Month (for non-commercial usage)
	 *
	 * @param $ip
	 * @return array|mixed|string
	 */
	public function getRaw($ip)
	{
		$apiKey = config('geoip.drivers.ipgeolocation.apiKey');
		$url = 'https://api.ipgeolocation.io/ipgeo';
		$query = [
			'apiKey' => $apiKey,
			'ip'     => $ip,
		];
		
		try {
			$response = Http::get($url, $query);
			if ($response->successful()) {
				return $response->json();
			}
		} catch (Throwable $e) {
			$response = $e;
		}
		
		return parseHttpRequestError($response);
	}
}
