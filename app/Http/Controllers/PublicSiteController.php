<?php

namespace App\Http\Controllers;

use App\Models\HostingPackage;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function home()
    {
        $packages = HostingPackage::where('is_active', true)->orderBy('price_ngn')->get();
        return view('public.home', compact('packages'));
    }

    public function services()
    {
        $packages = HostingPackage::where('is_active', true)->orderBy('price_ngn')->get();
        return view('public.services', compact('packages'));
    }
}
