<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResolveWishlistRequest;
use App\Http\Requests\Api\SyncWishlistRequest;
use App\Models\Customer;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request, WishlistService $wishlist): JsonResponse
    {
        abort_unless($request->user() instanceof Customer, 403);

        return response()->json($wishlist->read($request->user()->id));
    }

    public function sync(SyncWishlistRequest $request, WishlistService $wishlist): JsonResponse
    {
        return response()->json($wishlist->sync($request->user()->id, $request->validated('operations')));
    }

    public function resolve(ResolveWishlistRequest $request, WishlistService $wishlist): JsonResponse
    {
        return response()->json(['products' => $wishlist->resolve($request->validated('ids'))]);
    }
}
