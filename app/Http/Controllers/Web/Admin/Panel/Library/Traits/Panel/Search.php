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

namespace App\Http\Controllers\Web\Admin\Panel\Library\Traits\Panel;

use App\Helpers\Common\DBUtils;

trait Search
{
	/*
	|--------------------------------------------------------------------------
	|                                   SEARCH
	|--------------------------------------------------------------------------
	*/
	
	public bool $ajaxTable = true;
	
	/**
	 * Add conditions to the CRUD query for a particular search term.
	 *
	 * @param $searchTerm (Whatever string the user types in the search bar.)
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function applySearchTerm($searchTerm): \Illuminate\Database\Eloquent\Builder
	{
		return $this->query->where(function ($query) use ($searchTerm) {
			foreach ($this->getColumns() as $column) {
				if (!isset($column['type'])) {
					abort(400, 'Missing column type when trying to apply search term.');
				}
				
				$this->applySearchLogicForColumn($query, $column, $searchTerm);
			}
		});
	}
	
	/**
	 * Apply the search logic for each CRUD column.
	 *
	 * @param $query
	 * @param $column
	 * @param $searchTerm
	 * @return void
	 */
	public function applySearchLogicForColumn($query, $column, $searchTerm)
	{
		// If there's a particular search logic defined, apply that one
		if (isset($column['searchLogic'])) {
			$searchLogic = $column['searchLogic'];
			
			if (is_callable($searchLogic)) {
				return $searchLogic($query, $column, $searchTerm);
			}
			
			if (!$searchLogic) {
				return;
			}
		}
		
		// Sensible fallback search logic, if none was explicitly given
		if ($column['tableColumn']) {
			$singleSelectionFields = ['text', 'email'];
			$multiSelectionsFields = ['select_multiple', 'select'];
			
			// If the MySQL version is 8 or greater, don't use 'LIKE' statement for date or datetime column types
			if (!DBUtils::isMySqlMinVersion(8)) {
				$singleSelectionFields = array_merge($singleSelectionFields, ['date', 'datetime']);
			}
			
			if (in_array($column['type'], $singleSelectionFields)) {
				$query->orWhere($column['name'], 'like', '%' . $searchTerm . '%');
			} else if (in_array($column['type'], $multiSelectionsFields)) {
				$query->orWhereHas($column['entity'], function ($q) use ($column, $searchTerm) {
					$q->where($column['attribute'], 'like', '%' . $searchTerm . '%');
				});
			}
		}
	}
	
	/**
	 * Tell the list view to use AJAX for loading multiple rows.
	 *
	 * @deprecated 3.3.0 All tables are AjaxTables starting with 3.3.0.
	 */
	public function enableAjaxTable(): void
	{
		$this->ajaxTable = true;
	}
	
	/**
	 * Check if ajax is enabled for the table view.
	 *
	 * @return bool
	 * @deprecated 3.3.0 Since all tables use ajax, this will soon be removed.
	 */
	public function ajaxTable(): bool
	{
		return $this->ajaxTable;
	}
	
	/**
	 * Get the HTML of the cells in a table row, for a certain DB entry.
	 *
	 * @param $entry [Entity] A db entry of the current entity;
	 * @return array [array] Array of HTML cell contents.
	 */
	public function getRowViews($entry): array
	{
		$rowItems = [];
		foreach ($this->columns as $key => $column) {
			$rowItems[] = $this->getCellView($column, $entry);
		}
		
		// add the buttons as the last column
		if ($this->buttons->where('stack', 'line')->count()) {
			$rowItems[] = view('admin.panel.inc.button_stack', ['stack' => 'line'])
				->with('xPanel', $this)
				->with('entry', $entry)
				->render();
		}
		
		// add the details_row buttons as the first column
		if ($this->details_row) {
			array_unshift($rowItems, view('admin.panel.columns.details_row_button')
				->with('xPanel', $this)
				->with('entry', $entry)
				->render());
		}
		
		return $rowItems;
	}
	
	/**
	 * Get the HTML of a cell, using the column types.
	 *
	 * @param $column [array]
	 * @param $entry [Entity] A db entry of the current entity;
	 * @return string [HTML]
	 */
	public function getCellView($column, $entry): string
	{
		// if column type not set, show as text
		if (!isset($column['type'])) {
			return view('admin.panel.columns.text')
				->with('xPanel', $this)
				->with('column', $column)
				->with('entry', $entry)
				->render();
		} else {
			// if the column has been overwritten show that one
			if (view()->exists('vendor.admin.panel.columns.' . $column['type'])) {
				return view('vendor.admin.panel.columns.' . $column['type'])
					->with('xPanel', $this)
					->with('column', $column)
					->with('entry', $entry)
					->render();
			} else {
				// show the column from the package
				if (view()->exists('admin.panel.columns.' . $column['type'])) {
					return view('admin.panel.columns.' . $column['type'])
						->with('xPanel', $this)
						->with('column', $column)
						->with('entry', $entry)
						->render();
				} else {
					return view('admin.panel.columns.text')
						->with('xPanel', $this)
						->with('column', $column)
						->with('entry', $entry)
						->render();
				}
			}
		}
	}
	
	/**
	 * Created the array to be fed to the data table.
	 *
	 * @param $entries [Eloquent results].
	 * @param $totalRows
	 * @param $filteredRows
	 * @return array
	 */
	public function getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows): array
	{
		$rows = [];
		
		foreach ($entries as $row) {
			$rows[] = $this->getRowViews($row);
		}
		
		return [
			'draw'            => (isset($this->request['draw']) ? (int)$this->request['draw'] : 0),
			'recordsTotal'    => $totalRows,
			'recordsFiltered' => $filteredRows,
			'data'            => $rows,
		];
	}
}
