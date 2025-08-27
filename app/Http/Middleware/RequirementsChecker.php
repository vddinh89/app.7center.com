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

namespace App\Http\Middleware;

use App\Http\Controllers\Web\Setup\Install\Traits\CheckerTrait;
use Closure;
use Illuminate\Http\Request;

class RequirementsChecker
{
	use CheckerTrait;
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// Exception for the 'install' route
		if (isFromInstallProcess()) {
			return $next($request);
		}
		
		// Get eventual error message (due to lack of a requirement)
		$errorMessage = $this->getRequirementsErrors();
		
		// If no error message found, render the request response
		if (empty($errorMessage)) {
			return $next($request);
		}
		
		// If an error message found, show it.
		if (isFromApi()) {
			$errorMessage = !doesRequestIsFromWebClient() ? strip_tags($errorMessage) : $errorMessage;
			
			$result = [
				'success' => false,
				'message' => $errorMessage,
				'result'  => null,
			];
			
			return response()->json($result, 500, [], JSON_UNESCAPED_UNICODE);
			
		} else {
			if (isFromAjax($request)) {
				$result = [
					'success' => false,
					'msg'     => $errorMessage,
				];
				
				return response()->json($result, 500, [], JSON_UNESCAPED_UNICODE);
			} else {
				$errorMessage = '<strong style="color: green;">CAUSES & SOLUTIONS</strong><br>' . $errorMessage;
				
				return response()->view('front.errors.custom', ['message' => $errorMessage], 500);
			}
		}
	}
	
	/**
	 * Get eventual error message (due to lack of a requirement)
	 *
	 * @return string|null
	 */
	private function getRequirementsErrors(): ?string
	{
		// Get the system requirements (components & permissions)
		$requirements = array_merge($this->getComponents(), $this->getPermissions());
		if (empty($requirements)) {
			return null;
		}
		
		// Get eventual error message (due to lack of a requirement)
		$errorMessages = [];
		foreach ($requirements as $requirement) {
			if (
				!array_key_exists('permanentChecking', $requirement)
				|| !array_key_exists('required', $requirement)
				|| !array_key_exists('isOk', $requirement)
				|| !array_key_exists('name', $requirement)
			) {
				continue;
			}
			
			if ($requirement['permanentChecking'] && $requirement['required'] && !$requirement['isOk']) {
				$message = $requirement['warning'];
				
				// Customize the permissions errors message
				$anonymousDir = 'The directory';
				if (str_starts_with($message, $anonymousDir)) {
					$specificDir = $anonymousDir . ' <code>' . $requirement['name'] . '</code>';
					$message = str_replace($anonymousDir, $specificDir, $message);
				}
				
				$errorMessages[] = '- ' . $message;
			}
		}
		
		return $this->formatErrorsMessages($errorMessages);
	}
	
	/**
	 * @param array|null $errorMessages
	 * @return string|null
	 */
	private function formatErrorsMessages(?array $errorMessages): ?string
	{
		if (empty($errorMessages)) return null;
		
		return implode("\n", $errorMessages);
	}
}
