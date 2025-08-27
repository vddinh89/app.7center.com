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

namespace App\Services\User;

use App\Models\Payment;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\SavedSearch;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\StrictActiveScope;
use App\Models\Scopes\ValidPeriodScope;
use App\Models\Scopes\VerifiedScope;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

trait Stats
{
	/**
	 * @param $userId
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function getStats($userId): JsonResponse
	{
		$posts = [];
		$threads = [];
		$transactions = [];
		
		// posts (published)
		$posts['published'] = Post::query()
			->withoutAppends()
			->inCountry()->has('country')
			->where('user_id', $userId)
			->verified()
			->unarchived()
			->reviewed()
			->count();
		
		// posts (pendingApproval)
		$posts['pendingApproval'] = Post::query()
			->withoutAppends()
			->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
			->inCountry()->has('country')
			->where('user_id', $userId)
			->unverified()
			->count();
		
		// posts (archived)
		$posts['archived'] = Post::query()
			->withoutAppends()
			->inCountry()->has('country')
			->where('user_id', $userId)
			->archived()
			->count();
		
		// posts (visits)
		$postsVisits = DB::table((new Post())->getTable())
			->select('user_id', DB::raw('SUM(visits) as totalVisits'))
			->where('country_code', '=', config('country.code'))
			->where('user_id', $userId)
			->groupBy('user_id')
			->first();
		$posts['visits'] = $postsVisits->totalVisits ?? 0;
		
		// posts (favourite)
		$posts['favourite'] = SavedPost::query()
			->whereHas('post', fn ($query) => $query->inCountry()->has('country'))
			->where('user_id', $userId)
			->count();
		
		// savedSearch
		$savedSearch = SavedSearch::query()
			->inCountry()->has('country')
			->where('user_id', $userId)
			->count();
		
		// threads (all)
		$threads['all'] = Thread::query()
			->withoutAppends()
			->whereHas('post', fn ($query) => $query->inCountry()->has('country')->unarchived())
			->forUser($userId)
			->count();
		
		// threads (withNewMessage)
		$threads['withNewMessage'] = Thread::query()
			->withoutAppends()
			->whereHas('post', fn ($query) => $query->inCountry()->has('country')->unarchived())
			->forUserWithNewMessages($userId)
			->count();
		
		// transactions (promotion)
		$promotion = Payment::query()
			->withoutAppends()
			->withoutGlobalScopes([ValidPeriodScope::class, StrictActiveScope::class])
			->whereHasMorph('payable', Post::class, function ($query) use ($userId) {
				$query->inCountry()
					->has('country')
					->whereHas('user', fn ($query) => $query->where('user_id', $userId));
			})->whereHas('package', fn ($query) => $query->has('currency'))
			->count();
		$transactions['promotion'] = ($promotion > 0) ? $promotion : -1; // Set -1 to hide it in the sidebar menu
		
		// transactions (subscription)
		$subscription = Payment::query()
			->withoutAppends()
			->withoutGlobalScopes([ValidPeriodScope::class, StrictActiveScope::class])
			->whereHasMorph('payable', User::class, fn ($query) => $query->where('id', $userId))
			->whereHas('package', fn ($query) => $query->has('currency'))
			->count();
		$transactions['subscription'] = ($subscription > 0) ? $subscription : -1; // Set -1 to hide it in the sidebar menu
		
		// stats
		$stats = [
			'posts'        => $posts,
			'savedSearch'  => $savedSearch,
			'threads'      => $threads,
			'transactions' => $transactions,
		];
		
		$data = [
			'success' => true,
			'message' => null,
			'result'  => $stats,
		];
		
		return apiResponse()->json($data);
	}
}
