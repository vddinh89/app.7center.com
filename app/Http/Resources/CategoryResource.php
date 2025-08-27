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

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategoryResource extends BaseResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function toArray(Request $request): array
	{
		if (!isset($this->id)) return [];
		
		$entity = [
			'id' => $this->id,
		];
		
		$columns = $this->getFillable();
		foreach ($columns as $column) {
			$entity[$column] = $this->{$column} ?? null;
		}
		
		$entity['image_url'] = $this->image_url ?? null;
		
		if (in_array('parent', $this->embed)) {
			$entity['parent'] = new static($this->whenLoaded('parent'), $this->params);
		} else {
			$entity['parentClosure'] = new static($this->whenLoaded('parentClosure'), $this->params);
		}
		if (in_array('children', $this->embed)) {
			$children = $this->whenLoaded('children');
			$childrenCollection = new EntityCollection(CategoryResource::class, $children, $this->params);
			$entity['children'] = $childrenCollection->toArray(request(), true);
		}
		
		return $entity;
	}
}
