<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Link;
use App\Models\Product;
use App\Models\Sources;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function showcounts()
    {
       $productCount = Product::count();
       $sourceCount = Sources::count();
       $linksCount = Link::count();
    //    print_r($sourceCount);die;
        return view('dashboard.dashboard', compact( 'productCount','sourceCount','linksCount'));
    }
}
