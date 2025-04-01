<?php

namespace App\Http\Controllers\Dashboard;
use Carbon\Carbon;
use App\Models\Product;
use Illuminate\Http\Request;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function dashboard()
    {
        return view('dashboard.dashboard'); // Ensure `products.blade.php` exists
    }
        public function products()
    {
        return view('dashboard.products'); // Ensure `products.blade.php` exists
    }


    public function addproduct()
    {
        return view('dashboard.addproduct'); // Ensure `products.blade.php` exists
    }
    public function addsource()
    {
        return view('dashboard.addsource'); // Ensure `products.blade.php` exists
    }

    public function createproduct()
    {
        return view('dashboard.products');
    }

   public function storeproduct(Request $request)
   {
       $request->validate([
           'uin_no' => 'required',
           'product_name' => 'required|string',
           'source_id' => 'required',
           'product_slug' => 'required|string',
           'status' => 'required|in:0,1', // Ensuring only 0 or 1 is accepted
       ]);

       Product::create([

           'uin_no' => $request->uin_no,
           'product_name' => $request->product_name,
           'source_id' => $request->source_id,
           'product_slug' => $request->product_slug,
           'status' => (int) $request->status, // Convert to integer explicitly
           'created_on' => Carbon::now(), // Set current timestamp
           'updated_on' => Carbon::now(),
       ]);
       return redirect()->route('products')->with('success', 'source submitted successfully!');
   }

   public function showproduct()
{


    $Products = Product::all();

    return view('dashboard.products', compact('Products'));
}

public function export()
{
    return Excel::download(new ProductExport, 'product.xlsx');
}

}
