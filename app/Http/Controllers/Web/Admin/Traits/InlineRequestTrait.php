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

namespace App\Http\Controllers\Web\Admin\Traits;

use App\Http\Controllers\Web\Admin\Traits\InlineRequest\CountryTrait;
use App\Http\Controllers\Web\Admin\Traits\InlineRequest\PayableTrait;
use App\Http\Controllers\Web\Admin\Traits\InlineRequest\PaymentTrait;
use App\Http\Controllers\Web\Admin\Traits\InlineRequest\SectionTrait;
use Illuminate\Http\JsonResponse;

trait InlineRequestTrait
{
	use CountryTrait, SectionTrait, PayableTrait, PaymentTrait;
	
	// Types of column allowed to be updated on other conditions:
	// tinyint, date, datetime and timestamp
	protected array $tinyintColumnTypes = ['tinyint'];
	
	protected array $dateColumTypes = ['date', 'datetime', 'timestamp'];
	
	// Result Info
	protected bool $success = true;
	
	protected ?string $message = null;
	
	/**
	 * Update the specified column related to its table
	 *
	 * @param $model
	 * @param $column
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function updateData($model, $column): JsonResponse
	{
		$this->message = trans('admin.action_performed_successfully');
		
		// countries
		if ($this->table == 'countries' && $column == 'active') {
			return $this->updateCountryData($model, $column);
		}
		
		// payments
		if ($this->table == 'payments' && $column == 'active') {
			return $this->updatePaymentData($model, $column);
		}
		
		// payable (posts|users)
		if (in_array($this->table, ['posts', 'users']) && $column == 'featured') {
			return $this->updatePayableData($model, $column);
		}
		
		// sections
		if ($this->table == 'sections' && $column == 'active') {
			return $this->updateSectionData($model, $column);
		}
		
		// Check if the type is supported
		$allowedColumnTypes = array_merge($this->tinyintColumnTypes, $this->dateColumTypes);
		if (!in_array($this->columnType, $allowedColumnTypes)) {
			$error = trans('admin.inline_req_unsupported_column_type', ['columnType' => $this->columnType]);
			
			return $this->responseError($error, 400);
		}
		
		// Update the column data
		if (in_array($this->columnType, $this->tinyintColumnTypes)) {
			$model->{$column} = ($model->{$column} != 1) ? 1 : 0;
		}
		if (in_array($this->columnType, $this->dateColumTypes)) {
			$model->{$column} = (empty($model->{$column})) ? now() : null;
		}
		
		// Save data if something has changed
		if ($model->isDirty()) {
			$model->save();
		}
		
		return $this->responseSuccess($model, $column);
	}
	
	/**
	 * @param $model
	 * @param $column
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function responseSuccess($model, $column): JsonResponse
	{
		$columnValue = $model->{$column};
		
		$isToggleOn = false;
		if (in_array($this->columnType, $this->tinyintColumnTypes)) {
			$isToggleOn = ($columnValue == 1);
		}
		if (in_array($this->columnType, $this->dateColumTypes)) {
			$isToggleOn = !empty($columnValue);
		}
		
		// JS data
		$result = [
			'table'  => $this->table,
			'column' => $column,
			
			'success'    => $this->success,
			'message'    => $this->message,
			'isToggleOn' => $isToggleOn,
		];
		
		return ajaxResponse()->json($result);
	}
	
	/**
	 * @param string $error
	 * @param int $status
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function responseError(string $error, int $status = 500): JsonResponse
	{
		return ajaxResponse()->json(['message' => $error], $status);
	}
}
