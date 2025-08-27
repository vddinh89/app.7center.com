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

namespace App\Helpers\Common\GeoIP;

abstract class AbstractDriver
{
	public function __construct()
	{
		//...
	}
	
	/**
	 * Get GeoIP info from IP.
	 *
	 * @param string|null $ip
	 *
	 * @return array
	 */
	abstract public function get(?string $ip);
	
	/**
	 * Get the raw GeoIP info from the driver.
	 *
	 * @param string|null $ip
	 *
	 * @return mixed
	 */
	abstract public function getRaw(?string $ip);
	
	/**
	 * Get the default values (all null).
	 *
	 * @param string|null $ip
	 * @param $responseError
	 * @return array
	 */
	protected function getDefault(?string $ip, $responseError = null): array
	{
		$responseError = parseHttpRequestError($responseError); // required!
		
		return [
			'driver'      => config('geoip.default'),
			'ip'          => $ip,
			'error'       => $responseError,
			'city'        => null,
			'country'     => null,
			'countryCode' => null,
			'latitude'    => null,
			'longitude'   => null,
			'region'      => null,
			'regionCode'  => null,
			'timezone'    => null,
			'postalCode'  => null,
		];
	}
}
