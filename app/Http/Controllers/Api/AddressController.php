<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\OrderAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $addresses = OrderAddress::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $request->user()->id))
            ->where('hidden_from_checkout', false)
            ->latest('id')
            ->get()
            ->unique(fn (OrderAddress $address) => $this->addressKey($address))
            ->values();

        return AddressResource::collection($addresses);
    }

    public function destroy(Request $request, int $address): Response
    {
        $ownedAddresses = OrderAddress::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $request->user()->id))
            ->where('hidden_from_checkout', false);
        $selected = (clone $ownedAddresses)->findOrFail($address);
        $ids = $ownedAddresses->get()
            ->filter(fn (OrderAddress $candidate) => $this->addressKey($candidate) === $this->addressKey($selected))
            ->modelKeys();
        OrderAddress::query()->whereKey($ids)->update(['hidden_from_checkout' => true]);

        return response()->noContent();
    }

    private function addressKey(OrderAddress $address): string
    {
        return json_encode([
            mb_strtolower(trim($address->address)),
            mb_strtolower(trim($address->city)),
            preg_replace('/\s+/', '', $address->phone),
        ], JSON_THROW_ON_ERROR);
    }
}
