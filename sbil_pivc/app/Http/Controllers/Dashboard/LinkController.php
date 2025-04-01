<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Link;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LinkController extends Controller
{
    public function showlink()
    {
        $link = Link::all();

        return view('dashboard.links', compact('link'));
    }

    public function links()
    {
        return view('dashboard.links'); // Ensure `products.blade.php` exists
    }
}
