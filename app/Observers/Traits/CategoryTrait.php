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

namespace App\Observers\Traits;

use App\Helpers\Common\HierarchicalData\Library\AdjacentToNested;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\LanguageController;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

trait CategoryTrait
{
	/**
	 * Adding new nested nodes
	 *
	 * @param $category
	 * @return mixed
	 */
	protected function creatingNestedItem($category): mixed
	{
		// Find new left position & new depth
		$newLft = 0;
		$newDepth = 0;
		if (!empty($category->parent_id)) {
			// Node (will) have a parent
			$parent = Category::find($category->parent_id);
			
			if (!empty($parent)) {
				$newLft = $parent->lft; // <- Parent does not have children
				$newDepth = $parent->depth + 1;
				
				$lastChild = Category::childrenOf($parent->id)
					->where('id', '!=', $category->id)
					->orderByDesc('rgt')
					->first();
				
				if (!empty($lastChild)) {
					$newLft = $lastChild->rgt; // <- Parent has children
				}
			}
		} else {
			// Node (will) not have a parent
			$latest = Category::orderByDesc('rgt')->first();
			
			if (!empty($latest)) {
				$newLft = $latest->rgt;
			}
		}
		
		$tableName = (new Category())->getTable();
		
		// Create new space for subtree
		$affected = DB::table($tableName)->where('rgt', '>', $newLft)->update(['rgt' => DB::raw('rgt + 2')]);
		$affected = DB::table($tableName)->where('lft', '>', $newLft)->update(['lft' => DB::raw('lft + 2')]);
		
		// Update the lft, rgt & the depth columns for the new node
		$category->lft = $newLft + 1;
		$category->rgt = $newLft + 2;
		$category->depth = $newDepth;
		
		return $category;
	}
	
	/**
	 * Updating (Moving) nested nodes
	 *
	 * @param $category
	 * @return mixed
	 */
	protected function updatingNestedItem($category): mixed
	{
		// Escape from mass update
		if ($this->isFromMassUpdate()) {
			return $category;
		}
		
		// Get the original object values
		$original = $category->getOriginal();
		
		// Check some columns
		if (
			empty($original)
			|| !array_key_exists('parent_id', $original)
			|| !array_key_exists('lft', $original)
			|| !array_key_exists('rgt', $original)
		) {
			return $category;
		}
		
		// Since this method is not run during the reorder update,
		// don't update nodes if the 'parent_id' column is not changed
		if ($original['parent_id'] == $category->parent_id) {
			return $category;
		}
		
		// Find new left & right position & new depth
		$newLft = 0;
		$newDepth = 0;
		
		if (!empty($category->parent_id)) {
			// Node (will) have a parent
			$parent = Category::find($category->parent_id);
			
			if (!empty($parent)) {
				$newLft = $parent->lft; // <- Parent does not have children
				$newDepth = $parent->depth + 1;
				
				$lastChild = Category::childrenOf($parent->id)
					->where('id', '!=', $category->id)
					->orderByDesc('rgt')
					->first();
				
				if (!empty($lastChild)) {
					$newLft = $lastChild->rgt; // <- Parent has children
				}
			}
		} else {
			// Node (will) not have a parent
			$latest = Category::orderByDesc('rgt')->first();
			
			if (!empty($latest)) {
				$newLft = $latest->rgt;
			}
		}
		
		// Calculate position adjustment variables
		// Get space between rgt & lft + 1
		$width = $original['rgt'] - $original['lft'] + 1;
		
		$tableName = (new Category())->getTable();
		
		// Adding an existing node to a position (Moving a node)
		$affected = DB::table($tableName)->where('lft', '>', $newLft)->update(['lft' => DB::raw('lft + ' . $width)]);
		$affected = DB::table($tableName)->where('rgt', '>', $newLft)->update(['rgt' => DB::raw('rgt + ' . $width)]);
		
		// Update the new position & the depth column of the moved node
		$category->lft = $newLft + 1;
		$category->rgt = $newLft + $width;
		$category->depth = $newDepth;
		
		return $category;
	}
	
	/**
	 * Deleting nested nodes
	 *
	 * @param $category
	 */
	protected function deletingNestedItem($category): void
	{
		$tableName = (new Category())->getTable();
		
		// Get space between rgt & lft + 1
		$width = $category->rgt - $category->lft + 1;
		
		// Remove old space vacated by subtree (After deleting nodes)
		$affected = DB::table($tableName)->where('lft', '>', $category->rgt)->update(['lft' => DB::raw('lft - ' . $width)]);
		$affected = DB::table($tableName)->where('rgt', '>', $category->rgt)->update(['rgt' => DB::raw('rgt - ' . $width)]);
	}
	
	/**
	 * Delete the category's children recursively
	 *
	 * @param $category
	 */
	protected function deleteChildrenRecursively($category): void
	{
		if (!empty($category) && isset($category->id)) {
			$subCats = Category::childrenOf($category->id)->get();
			if ($subCats->count() > 0) {
				foreach ($subCats as $subCat) {
					if (isset($subCat->children) && $subCat->children->count() > 0) {
						$this->deleteChildrenRecursively($subCat);
					}
					
					$subCat->delete();
				}
			}
		}
	}
	
	/**
	 * Convert Adjacent List to Nested Set (By giving the Item's Language)
	 * NOTE: Need to use adjacent list model to add, update or delete nodes
	 *
	 * @param $category
	 * @return void
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function adjacentToNestedByItem($category): void
	{
		// Escape from mass update
		if ($this->isFromMassUpdate()) {
			return;
		}
		
		$tableName = (new Category())->getTable();
		
		$params = [
			'adjacentTable' => $tableName,
			'nestedTable'   => $tableName,
		];
		
		$transformer = new AdjacentToNested($params);
		
		$transformer->getAndSetAdjacentItemsIds();
		$transformer->convertChildrenRecursively(0);
		$transformer->setNodesDepth();
	}
	
	/**
	 * Fix required columns
	 *
	 * @param $category
	 * @return mixed
	 */
	protected function fixRequiredColumns($category): mixed
	{
		// The 'type' column is a not nullable enum, so required
		if (isset($category->type) && empty($category->type)) {
			if (!empty($category->parent)) {
				if (!empty($category->parent->type)) {
					$category->type = $category->parent->type;
				}
			}
			if (empty($category->type)) {
				$category->type = 'classified';
			}
		}
		
		return $category;
	}
	
	/**
	 * Escape from mass update
	 *
	 * @return bool
	 */
	private function isFromMassUpdate(): bool
	{
		// Escape from mass update. ie:
		// - CategoryController (only for reorder() & saveReorder() methods)
		// - LanguageController (all methods)
		if (
			request()->is('*/reorder')
			|| str_contains(currentRouteAction(), CategoryController::class . '@reorder')
			|| str_contains(currentRouteAction(), CategoryController::class . '@saveReorder')
			|| str_contains(currentRouteAction(), LanguageController::class)
		) {
			return true;
		}
		
		return false;
	}
}
