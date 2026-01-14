<?php

namespace App\Http\Controllers;

use App\Models\PortalLink;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $links = PortalLink::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('welcome', compact('links'));
    }
}
