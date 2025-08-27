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

namespace App\Helpers\Services\Search\Traits\Filters;

use App\Models\Post;
use Illuminate\Support\Facades\Schema;

trait DynamicFieldsFilter
{
	protected array $filterParametersFields = [
		// 'getKey' => 'tableColumn',
		// ...
	];
	
	protected function applyDynamicFieldsFilters(): void
	{
		if (!(isset($this->posts) && isset($this->having))) {
			return;
		}
		
		$parameters = $this->input;
		if (empty($parameters)) {
			return;
		}
		
		foreach ($parameters as $key => $value) {
			if (!isset($this->filterParametersFields[$key])) {
				continue;
			}
			if (!is_array($value) && trim($value) == '') {
				continue;
			}
			
			$table = (new Post())->getTable();
			if (is_array($value)) {
				$tmpArr = [];
				foreach ($value as $k => $v) {
					if (is_array($v)) continue;
					if (!is_array($v) && trim($v) == '') continue;
					
					$tmpArr[$k] = $v;
				}
				if (!empty($tmpArr)) {
					if (Schema::hasColumn($table, $this->filterParametersFields[$key])) {
						$this->posts->whereIn($this->filterParametersFields[$key], $tmpArr);
					}
				}
			} else {
				if (Schema::hasColumn($table, $this->filterParametersFields[$key])) {
					$this->posts->where($this->filterParametersFields[$key], $value);
				}
			}
		}
	}
}
