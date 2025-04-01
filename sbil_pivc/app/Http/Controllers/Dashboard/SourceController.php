<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;

use App\Models\Sources;
use Illuminate\Http\Request;
use App\Exports\sourcesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class SourceController extends Controller
{

    public function source()
    {
        return view('dashboard.source'); // Ensure `products.blade.php` exists
    }
    public function createsource()
    {
        return view('dashboard.source');
    }

   public function storesource(Request $request)
   {
       $request->validate([
           'name' => 'required|string',
           'desc' => 'required|string',
           'status' => 'required|in:0,1', // Ensuring only 0 or 1 is accepted
       ]);

       Sources::create([

           'name' => $request->name,
           'desc' => $request->desc,
           'status' => (int) $request->status, // Convert to integer explicitly
           'created_on' => Carbon::now(), // Set current timestamp
           'updated_on' => Carbon::now(),
       ]);
       return redirect()->route('source')->with('success', 'source submitted successfully!');
   }

   public function showsource()
   {

      $Source = Sources::all();

      return view('dashboard.source', compact('Source'));
   }

   public function export()
{
    return Excel::download(new sourcesExport, 'source.xlsx');
}
}

