<?php
namespace App\Services;

use DB;
use App\Models\Link;
use App\Models\logs;
use App\Models\Client;
use App\Services\Request;
use Illuminate\Support\Str;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\CommonRepository;

class KfdService
{
    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function checkLinkKeyExist($key)
    {

        $result = DB::table(config('constants.LINKS_TABLE'))
            ->select('id','consent_image_url','reg_photo_url')
            ->where('ukey', $key)
            ->where('status', 1)
            ->first();

        return $result ? (array) $result : false;
    }

    
   




}







