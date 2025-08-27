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

use Illuminate\Http\RedirectResponse;
use Throwable;

trait SettingsTrait
{
	/**
	 * @param $id
	 * @param null $childId
	 * @return \Illuminate\View\View
	 */
	public function edit($id, $childId = null)
	{
		$this->xPanel->hasAccessOrFail('update');
		
		$entry = null;
		if (!empty($childId)) {
			$entry = $this->xPanel->getEntryWithParentAndChildKeys($id, $childId);
			$id = $childId;
		}
		
		$this->data['entry'] = (!empty($entry)) ? $entry : $this->xPanel->getEntry($id);
		
		// Add the 'field' field
		$fieldColValue = (is_array($this->data['entry']->field))
			? $this->data['entry']->field
			: json_decode($this->data['entry']->field, true);
		
		$this->addField($fieldColValue);
		
		// ...
		$this->data['xPanel'] = $this->xPanel;
		$this->data['saveAction'] = $this->getSaveAction();
		$this->data['fields'] = $this->xPanel->getUpdateFields($id);
		$this->data['title'] = trans('admin.edit') . ' ' . $this->xPanel->entityName;
		
		$this->data['id'] = $id;
		
		return view('admin.panel.edit', $this->data);
	}
	
	/**
	 * @param $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateTrait($request): RedirectResponse
	{
		$this->data['entry'] = $this->xPanel->getEntry($request->input('id'));
		
		// Add the 'field' field
		$fieldColValue = (is_array($this->data['entry']->field))
			? $this->data['entry']->field
			: json_decode($this->data['entry']->field, true);
		
		$this->addField($fieldColValue);
		
		return parent::updateCrud($request);
	}
	
	/**
	 * Add fake fields as an array of the default json
	 *
	 * @param $fieldColValue
	 * @return void
	 */
	public function addField($fieldColValue): void
	{
		// Get the fake feature items
		$fakeFeatureItems = [
			'fake'     => true,
			'store_in' => "value",
		];
		
		// Is a multi-fields settings
		if (isset($fieldColValue[0]) && is_array($fieldColValue[0])) {
			foreach ($fieldColValue as $key => $fieldColItem) {
				if (!is_array($fieldColItem)) continue;
				
				try {
					$fieldColItemFull = array_merge($fieldColItem, $fakeFeatureItems);
					$this->addField($fieldColItemFull);
				} catch (Throwable $e) {
				}
			}
		} else {
			// Is a one field settings (with valid json data)
			if (isset($fieldColValue['name'])) {
				if (isset($fieldColValue['label'])) {
					if (isset($fieldColValue['autoTrans'])) {
						if (isset($fieldColValue['plugin'])) {
							$fieldColValue['label'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['label']);
						} else {
							$fieldColValue['label'] = trans('admin.' . $fieldColValue['label']);
						}
					}
				}
				
				if (isset($fieldColValue['hint'])) {
					if (isset($fieldColValue['autoTrans'])) {
						$checkClearedHintContent = trim(strip_tags($fieldColValue['hint']));
						if (!empty($checkClearedHintContent)) {
							if (isset($fieldColValue['plugin'])) {
								$fieldColValue['hint'] = trans($fieldColValue['plugin'] . '::messages.' . $fieldColValue['hint']);
							} else {
								$fieldColValue['hint'] = trans('admin.' . $fieldColValue['hint']);
							}
						}
					}
					$fieldColValue['hint'] = str_replace('{adminUrl}', urlGen()->adminUrl(), $fieldColValue['hint']);
				}
				
				if (isset($fieldColValue['type']) && $fieldColValue['type'] == 'custom_html') {
					if (isset($fieldColValue['autoTrans'])) {
						$checkClearedValueContent = trim(strip_tags($fieldColValue['value']));
						if (!empty($checkClearedValueContent)) {
							$fieldColValue['value'] = trans('admin.' . $fieldColValue['value']);
						}
					}
					$fieldColValue['value'] = str_replace('{adminUrl}', urlGen()->adminUrl(), $fieldColValue['value']);
				}
			} else {
				// Is a one field settings (without valid json data)
				$fieldColValue = [
					'name'  => 'value',
					'label' => 'Value',
					'type'  => 'text',
				];
			}
			
			// Add the fake field to xPanel
			$this->xPanel->addField($fieldColValue);
		}
	}
}
