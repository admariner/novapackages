<?php

namespace App\Http\Controllers\InternalApi;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Package;
use App\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use willvincent\Rateable\Rating;

class ReviewsController extends Controller
{
    public function store()
    {
        request()->validate([
            'package_id' => [
                'required',
                'exists:App\Package,id',
            ],
            'review' => 'required',
        ]);

        Package::findOrFail(request('package_id'))
            ->addReview(
                Rating::where('rateable_id', request('package_id'))->where('user_id', auth()->id())->first()->id,
                request('review')
            );

        return ['status' => 'success', 'message' => 'Review created successfully'];
    }

    public function update()
    {
        request()->validate([
            'package_id' => [
                'required',
                'exists:App\Package,id',
            ],
            'review' => 'required',
        ]);

        Package::findOrFail(request('package_id'))->updateReview(request('review'));

        return ['status' => 'success', 'message' => 'Review edited successfully'];
    }

    public function destroy(Review $review): Response
    {
        $review->delete();

        return response('Success', 200);
    }
}
