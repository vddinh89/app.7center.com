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

class MaxmindApi extends AbstractDriver
{
	public function get($ip)
	{
		$data = $this->getRaw($ip);
		
		if (empty($data) || (data_get($data, 'status') === 'fail') || is_string($data)) {
			return $this->getDefault($ip, $data);
		}
		
		return [
			'driver'      => config('geoip.default'),
			'ip'          => $ip,
			'city'        => null,
			'country'     => data_get($data, 'country.names.en'),
			'countryCode' => data_get($data, 'country.iso_code'),
			'latitude'    => null,
			'longitude'   => null,
			'region'      => null,
			'regionCode'  => null,
			'timezone'    => null,
			'postalCode'  => null,
		];
	}
	
	/**
	 * maxmind_api
	 * https://www.maxmind.com/
	 * Free Plan: Available Web Service Funds $5.00
	 * Queries remaining
	 * - GeoIP2 Country    50,000
	 * - GeoIP2 City Plus  16,666
	 * - GeoIP2 Insights   2,500
	 * https://dev.maxmind.com/geoip/docs/web-services
	 *
	 * @param $ip
	 * @return array|mixed|string
	 */
	public function getRaw($ip)
	{
		$accountId = config('geoip.drivers.maxmind_api.accountId');
		$licenseKey = config('geoip.drivers.maxmind_api.licenseKey');
		$url = 'https://geoip.maxmind.com/geoip/v2.1/country/' . $ip . '?pretty';
		
		try {
			$response = Http::withBasicAuth($accountId, $licenseKey)->get($url);
			if ($response->successful()) {
				return $response->json();
			}
		} catch (Throwable $e) {
			$response = $e;
		}
		
		return parseHttpRequestError($response);
	}
}
