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

class LinkService
{
    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }
    public function checkProposalNoStatusCheck($proposalNo)
    {

       $proposalDetails =  Link::where('proposal_no', strtoupper($proposalNo))
            ->with('product:id,product_name,uin_no') // Fetch related product fields
            ->orderBy('version', 'desc')
            ->first();

        return $proposalDetails ? $proposalDetails->toArray() : false;
    }

    public function checkClientExist($whereArr)
    {
        $clientsTable = config('constants.CLIENTS_TABLE');

        return $this->commonRepository->select('row_array', ['id', 'ckey', 'short_name'], $clientsTable, $whereArr,NULL,NULL,0);

    }

    public function generateLinkUid()
    {
        do {
            $id = $this->generateId();
        } while ($this->checkLinkUidExist($id));

        return $id;
    }

    public function generateLinkKey()
    {
        do {
            $id = $this->generateId();
        } while ($this->checkLinkKeyExist($id));

        return $id;
    }

    private function generateId()
    {
        return Str::uuid()->toString(); // Generates a unique identifier
    }

    private function checkLinkUidExist($key)
    {
        return \DB::table(config('constants.LINKS_TABLE'))
        ->where('uid', strtolower($key))
        ->where('status', 1)
        ->exists();
    }

    public function checkLinkKeyExist($key)
    {
       
        $result = DB::table(config('constants.LINKS_TABLE'))
        ->where('ukey', strtolower($key))
        ->where('status', 1)
        ->first();

    return $result ? (array) $result : false;

    }
    public static function clearResponseNull($key)
    {
        return DB::table(config('constants.LINKS_TABLE'))
            ->where('ukey', $key)
            ->where('status', 1)
            ->update(['response' => null, 'disagree_status' => 0]);
    }



    public function countProposalNo($proposalNo)
    {

        return DB::table(config('constants.LINKS_TABLE'))
            ->where('proposal_no', strtoupper($proposalNo))
            ->count();
    }
    public function saveLink(array $link)
    {

        if (!empty($link)) {

            return $this->commonRepository->insert(config('constants.LINKS_TABLE'), $link, 'id');
        }
        return false;
    }
        public function addLog($key, $input_json, $output_json, $fail = 0)
        {
        $insert_data = [
            'slug' => $key,
            'input_msg' => $input_json,
            'output_msg' => $output_json,
            'fail_status' => $fail ? 1 : 0
        ];

        return logs::create($insert_data);
        }

        public function checkProposalData($proposalNo)
        {
            return Link::select('links.*', 'products.product_name', 'products.uin_no')
                ->where('links.proposal_no', strtoupper($proposalNo))
                ->where('links.status', 1)
                ->join('products', 'products.id', '=', 'links.product_id')
                ->first();
        }

        public static function getUrlUuid($url)
            {
                // Extract query string from URL
                $queryString = parse_url($url, PHP_URL_QUERY);

                if ($queryString) {

                    // Ensure the decryptString method exists and works correctly
                    $decryptedQueryString = CommonHelper::decryptString($queryString);
                   // print_r($decryptedQueryString);die;
                    if ($decryptedQueryString) {
                        parse_str($decryptedQueryString, $queryArray);

                        // Ensure checkHadValue exists and is used correctly
                        return isset($queryArray['uuid']) ? self::checkHadValue($queryArray['uuid'], false) : false;
                    }
                }


                return false;
            }

            public function checkPivcUidExist($uid)
            {
                return DB::table('links')
                    ->select('id')
                    ->where('uid', $uid)
                    ->where('status', 1)
                    ->first() ?? false;
            }
            public function getPIVCLinkDetail($id)
            {
                if (empty($id)) {
                    return null; // Return null if ID is not valid
                }

                return DB::table('links as l')
                    ->select('l.*', DB::raw('CURDATE() as cur_date'))
                    ->where('l.id', $id)
                    ->first();
            }


            public function validateProduct($prodId, $srcId)
            {
                $product = DB::table('products')
                    ->select('id', 'uin_no', 'product_slug', 'status', 'product_name')
                    ->where('id', $prodId)
                    ->where('source_id', $srcId)
                    ->where('status', 1)
                    ->first();

                return $product ? (array) $product : false;
            }





        public static function checkHadValue($value, $default = false)
        {
            return !empty($value) ? $value : $default;
        }



        public function getGeoAddress($lat, $long)
        {

            $address = '';

            // HERE API
            $apiKey = 'B33HyEflBtyFEnKpObnuxgOhDCsoDuahdyE8GYNjBus';
            $response = Http::get("https://revgeocode.search.hereapi.com/v1/revgeocode", [
                'at' => "{$lat},{$long}",
                'lang' => 'en-US',
                'apiKey' => $apiKey
            ]);

            if ($response->successful() && isset($response['items'][0]['address']['label'])) {
                $address = $response['items'][0]['address']['label'];
            }

            // Google API (if HERE API fails)
            if ($address == "") {
                $googleApiKey = 'AIzaSyBPnS82bRgH3-yYqK_-ikTWzKqS5P5n63g';
                $googleResponse = Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
                    'latlng' => "{$lat},{$long}",
                    'key' => $googleApiKey
                ]);

                if ($googleResponse->successful() && isset($googleResponse['results'][0]['formatted_address'])) {
                    $address = $googleResponse['results'][0]['formatted_address'];
                }
            }

            // HERE API (Backup Key)
            if ($address == "") {
                $backupApiKey = 'fgRzArsFrInbcZ3b51duu7p6T65NGRHWEHvgntuEj2I';
                $backupResponse = Http::get("https://revgeocode.search.hereapi.com/v1/revgeocode", [
                    'at' => "{$lat},{$long}",
                    'lang' => 'en-US',
                    'apiKey' => $backupApiKey
                ]);

                if ($backupResponse->successful() && isset($backupResponse['items'][0]['address']['label'])) {
                    $address = $backupResponse['items'][0]['address']['label'];
                }
            }

            // OpenStreetMap API
            if ($address == "") {
                $osmResponse = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0'
                ])->get("https://nominatim.openstreetmap.org/reverse", [
                    'format' => 'geocodejson',
                    'lat' => $lat,
                    'lon' => $long,
                    'zoom' => 18
                ]);

                if ($osmResponse->successful() && isset($osmResponse['features'][0]['properties']['geocoding']['label'])) {
                    $address = $osmResponse['features'][0]['properties']['geocoding']['label'];
                }
            }

            // OpenCage API
            if ($address == "") {
                $openCageApiKey = '7525b3d0bc384ecdb847fddf4c41b788';
                $openCageResponse = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0'
                ])->get("https://api.opencagedata.com/geocode/v1/json", [
                    'key' => $openCageApiKey,
                    'q' => "{$lat}, {$long}",
                    'pretty' => 1,
                    'no_annotations' => 1
                ]);

                if ($openCageResponse->successful() && isset($openCageResponse['results'][0]['formatted'])) {
                    $address = $openCageResponse['results'][0]['formatted'];
                }
            }

            return $address;
        }

        public function setDeviceDetails($link_id,$device)
        {
            //print_r($link_id);die;
            return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $link_id], ['device' => $device]);

        }

      
        public function clearDisAgreeStatus($linkId)
            {
              //  return Link::where('id', $linkId)->update(['disagree_status' => 0]);

                return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $linkId],['disagree_status' => 0]);
            }
        
        public  function updateLinkResponseNew($link_id, $link_configKey, $configParams)
        {

            $link_details = self::getPIVCLinkDetail($link_id);

            if (!empty($link_details)) {
                $link_response = self::checkHadValue($link_details->response);
                $res_arr = [];

                if (!empty($link_details->response)) {
                    $res_arr = json_decode($link_response, true);

                    if ($configParams['agree_status']) {
                        $ky = ltrim($link_configKey, 'c');
                        $edit = 'e' . $ky;
                        unset($res_arr[$link_configKey]);
                        unset($res_arr[$edit]);
                        $stus = CommonHelper::pivcRemarks(true, true, $res_arr);
                        if ($stus === "Clear Case") {
                            self::clearDisAgreeStatus($link_id);
                        }
                    }

                    $res_arr[$link_configKey] = $configParams;
                } else {
                    $res_arr[$link_configKey] = $configParams;
                }

                $res_json = json_encode($res_arr);

                self::updateResponse($link_id, $res_json);

                return true;
            }

            return false;
        }
        public function updateResponse($linkId, $response)
        {

            return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $linkId],['response' => $response]);
        }
        public function setDisAgreeStatus($link_id)
        {

            return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $link_id],['disagree_status' => 1]);
        }

        public function updateLinkResponse($link_id,$link_configKey,$configParams)
        {
            $link_details = self::getPIVCLinkDetail($link_id);

            if(!empty($link_details))
            {

                $link_response = self::checkHadValue($link_details->response);

                $res_arr = array();

                if($link_response)
                {
                    $res_arr = json_decode($link_response,true);

                    $res_arr[$link_configKey]=$configParams;
                }
                else
                {
                    $res_arr[$link_configKey]=$configParams;
                }

                $res_json = json_encode($res_arr);

                self::updateResponse($link_id,$res_json);

                return TRUE;
            }
            else
            {
                return FALSE;
            }
        }
    

}







