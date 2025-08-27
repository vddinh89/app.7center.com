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

namespace App\Services\Thread;

use App\Models\Thread;
use Illuminate\Http\JsonResponse;

trait UpdateByTypeTrait
{
	/**
	 * @param $ids
	 * @param string|null $actionType
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function updateByType($ids, ?string $actionType = null): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->forbidden();
		}
		
		if ($actionType == 'markAsRead') {
			return $this->markAsRead($ids, $authUser);
		}
		if ($actionType == 'markAsUnread') {
			return $this->markAsUnread($ids, $authUser);
		}
		if ($actionType == 'markAsImportant') {
			return $this->markAsImportant($ids, $authUser);
		}
		if ($actionType == 'markAsNotImportant') {
			return $this->markAsNotImportant($ids, $authUser);
		}
		if ($actionType == 'markAllAsRead') {
			return $this->markAllAsRead($authUser);
		}
		
		return apiResponse()->forbidden();
	}
	
	/**
	 * @param $ids
	 * @param $user
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function markAsRead($ids, $user): JsonResponse
	{
		foreach ($ids as $id) {
			// Get the Thread
			$thread = $this->findThread($id);
			
			if (!empty($thread)) {
				$thread->markTheThreadAsRead($user->id);
			}
		}
		
		$count = count($ids);
		if ($count > 1) {
			$msg = t('x entities have been marked as action successfully', [
				'entities' => t('messages'),
				'count'    => $count,
				'action'   => mb_strtolower(t('read')),
			]);
		} else {
			$msg = t('1 entity has been marked as action successfully', [
				'entity' => t('message'),
				'action' => mb_strtolower(t('read')),
			]);
		}
		
		return apiResponse()->success($msg);
	}
	
	/**
	 * @param $ids
	 * @param $user
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function markAsUnread($ids, $user): JsonResponse
	{
		foreach ($ids as $id) {
			// Get the Thread
			$thread = $this->findThread($id);
			
			if (!empty($thread)) {
				$thread->markTheThreadAsUnread($user->id);
			}
		}
		
		$count = count($ids);
		if ($count > 1) {
			$msg = t('x entities have been marked as action successfully', [
				'entities' => t('messages'),
				'count'    => $count,
				'action'   => mb_strtolower(t('unread')),
			]);
		} else {
			$msg = t('1 entity has been marked as action successfully', [
				'entity' => t('message'),
				'action' => mb_strtolower(t('unread')),
			]);
		}
		
		return apiResponse()->success($msg);
	}
	
	/**
	 * @param $ids
	 * @param $user
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function markAsImportant($ids, $user): JsonResponse
	{
		foreach ($ids as $id) {
			// Get the Thread
			$thread = $this->findThread($id);
			
			if (!empty($thread)) {
				$thread->markAsImportant($user->id);
			}
		}
		
		$count = count($ids);
		if ($count > 1) {
			$msg = t('x entities have been marked as action successfully', [
				'entities' => t('messages'),
				'count'    => $count,
				'action'   => mb_strtolower(t('important')),
			]);
		} else {
			$msg = t('1 entity has been marked as action successfully', [
				'entity' => t('message'),
				'action' => mb_strtolower(t('important')),
			]);
		}
		
		return apiResponse()->success($msg);
	}
	
	/**
	 * @param $ids
	 * @param $user
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function markAsNotImportant($ids, $user): JsonResponse
	{
		foreach ($ids as $id) {
			// Get the Thread
			$thread = $this->findThread($id);
			
			if (!empty($thread)) {
				$thread->markAsNotImportant($user->id);
			}
		}
		
		$count = count($ids);
		if ($count > 1) {
			$msg = t('x entities have been marked as action successfully', [
				'entities' => t('messages'),
				'count'    => $count,
				'action'   => mb_strtolower(t('not important')),
			]);
		} else {
			$msg = t('1 entity has been marked as action successfully', [
				'entity' => t('message'),
				'action' => mb_strtolower(t('not important')),
			]);
		}
		
		return apiResponse()->success($msg);
	}
	
	/**
	 * Mark all Threads as read
	 *
	 * @param $user
	 * @return \Illuminate\Http\JsonResponse
	 */
	private function markAllAsRead($user): JsonResponse
	{
		// Get all Threads with New Messages
		$threadsWithNewMessage = Thread::query()
			->whereHas('post', fn ($query) => $query->inCountry())
			->forUserWithNewMessages($user->id);
		
		// Count all Threads
		$countThreadsWithNewMessage = $threadsWithNewMessage->count();
		
		if ($countThreadsWithNewMessage > 0) {
			foreach ($threadsWithNewMessage->cursor() as $thread) {
				$thread->markTheThreadAsRead($user->id);
			}
			$msg = t('x entities have been marked as action successfully', [
				'entities' => t('messages'),
				'count'    => $countThreadsWithNewMessage,
				'action'   => mb_strtolower(t('read')),
			]);
			
			return apiResponse()->success($msg);
		} else {
			$msg = t('This action could not be done');
			
			return apiResponse()->error($msg);
		}
	}
	
	/* PRIVATE */
	
	/**
	 * @param $id
	 * @return mixed
	 */
	private function findThread($id): mixed
	{
		$authUser = auth(getAuthGuard())->user();
		
		return Thread::where((new Thread)->getTable() . '.id', $id)
			->forUser($authUser->getAuthIdentifier())
			->first();
	}
}
