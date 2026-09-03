<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LogisticResource;
use App\Models\Logistic;
use Illuminate\Http\Request;

class LogisticController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logistic = Logistic::all();
        return LogisticResource::collection($logistic);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
