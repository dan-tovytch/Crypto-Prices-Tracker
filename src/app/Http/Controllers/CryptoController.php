<?php

namespace App\Http\Controllers;

use App\Http\Resources\CryptoResource;
use App\Models\CryptoPrices;
use Inertia\Inertia;

class CryptoController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Dashboard', [
            'coins' => CryptoResource::collection(
                CryptoPrices::orderBy('market_cap_rank')->get()
            )->resolve(),
        ]);
    }
}
