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

class ThreadMessageResource extends BaseResource
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
		
		$entity['created_at_formatted'] = $this->created_at_formatted ?? null;
		$entity['p_recipient'] = $this->p_recipient ?? null;
		
		if (in_array('thread', $this->embed)) {
			$entity['thread'] = new ThreadResource($this->whenLoaded('thread'), $this->params);
		}
		if (in_array('user', $this->embed)) {
			$entity['user'] = new UserResource($this->whenLoaded('user'), $this->params);
		}
		
		return $entity;
	}
}
