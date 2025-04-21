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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
 
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
        public function updateLinkResponseRinnRiksha($linkId, $linkConfigKey, $configParams, $comboNumber = "")
        {
            $linkDetails = $this->getPIVCLinkDetail($linkId);
           
            if (!empty($linkDetails)) {
                $linkResponse = self::checkHadValue($linkDetails->response)?? '';  
                $resArr = !empty($linkResponse) ? json_decode($linkResponse, true) : [];
               
                if ($configParams['page'] === 'Medical Confirmation Screen One') {
                    $medicalCondition = strtolower($configParams['input']['medicalConditionPresent'] ?? '');
                    $treatmentLast5Years = strtolower($configParams['input']['treatmentLast_5years'] ?? '');
        
                    if (($medicalCondition === 'no' || $medicalCondition === 'n') && ($treatmentLast5Years === 'no' || $treatmentLast5Years === 'n')) {
                        $configParams['agree_status'] = false;
                        $agreename = 'cMedicalQuestionOne';
                        
                        if (!isset($resArr['ePerDet']) && !isset($resArr['eMedQuest'])) {
                            $this->clearDisAgreeStatus($linkId);
                        }
                    } else {
                        $configParams['agree_status'] = true;
                        $agreename = 'eMedicalQuestionOne';
                        
                        $this->setDisAgreeStatus($linkId);
                    }
                    
                    $resArr[$agreename] = $configParams; 
                } elseif ($configParams['page'] === 'Medical Confirmation Screen Two') {
                    $cond = [];
                    $reviewResponse = strtolower($configParams['input']['reviewProposalResponse'] ?? '');
                    
                    foreach ($configParams['input'] as $key => $val) {
                        $valLower = strtolower($val);
                        if ($reviewResponse === 'yes' && $key !== 'reviewProposalResponse') {
                            if (in_array($valLower, ['yes', 'yes_edit', 'no_edit'])) {
                                $cond[] = "medical_dispute";
                            } elseif ($valLower === 'no') {
                                $cond[] = 'clearcase';
                            }
                        } elseif ($reviewResponse === 'no') {
                            $cond[] = 'clearcase';
                            $configParams['agree_status'] = false;
                        }
                    }
                    
                    if ($reviewResponse === 'no' && !in_array('medical_dispute', $cond)) {
                        $agreename = 'cMedicalQuestionTwo';
                        if (!isset($resArr['ePerDet']) && !isset($resArr['eMedQuest']) && !isset($resArr['eMedicalQuestionOne'])) {
                            $this->clearDisAgreeStatus($linkId);
                        }
                    } elseif ($reviewResponse === 'yes' && in_array('medical_dispute', $cond)) {
                        $agreename = 'eMedicalQuestionTwo';
                        $this->setDisAgreeStatus($linkId);
                    }
                    
                    $resArr[$agreename] = $configParams;
                }
        
                $resJson = json_encode($resArr);
                $this->updateResponse($linkId, $resJson);
        
                return true;
            }
            
            return false;
        }
        
  public function setCompleteStatus($link_id)
  {
      $cDate = Carbon::now();

      $link = Link::select('source', 'product_id', 'params', 'video_url', 'consent_image_url', 'reg_photo_url')
                  ->where('id', $link_id)
                  ->first();

      if (!$link) {

          return response()->json(['error' => 'Link not found'], 404);
      }

      $p_id = $link->product_id;
      $s_id = $link->source;
      $video = $link->video_url;

      $params = json_decode($link->params);
      $flowdata = $params->flow_data ?? null;

      // Determine risk profile
      if (isset($flowdata->RISK_PROFILE) && strtolower($flowdata->RISK_PROFILE) === 'high') {
          $risk = strtolower($flowdata->RISK_PROFILE);
      } else {
          $risk = 'low';
      }

      // Decode JSON fields from DB
      $consent = $link->consent_image_url;
      $reg = $link->reg_photo_url;

      $con_obj = json_decode($consent, true);
      $reg_obj = json_decode($reg, true);

      // Define product ID array
      $arr_p_id = [5, 67, 68, 69, 70, 79, 80, 81, 82, 143, 144, 147, 148];
     
      if (!empty($link->consent_image_url) || !empty($link->reg_photo_url)) {
          // Update the record using Eloquent
          return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $link_id], array('complete_status'=>1,'completed_on'=>$cDate));
       /*    return $this->common_model->update(LINKS_TABLE, array('id'=>$link_id), array('complete_status'=>1,'completed_on'=>$cDate));
          $link->update([
              'complete_status' => 1,
              'completed_on' => $cDate, // Assuming the table has this column
          ]);
          print_r($link);die; */
          return true;
      } else {
          return false;
      }

  }
  public function updateGroupPIWCDetailsInsta(array $pivcData): bool
  {
      $params = [
          'UpdateGroupPIWCDetails_Insta' => [
              'FORM_NUM'          => $pivcData['FORM_NUM'],
              'PL_POL_NUM'        => $pivcData['PL_POL_NUM'],
              'LOAN_ACCT_NUM'     => $pivcData['LOAN_ACCT_NUM'],
              'LOAN_PLUS_ACCT_NUM'=> $pivcData['LOAN_PLUS_ACCT_NUM'],
              'PIWC_CALL_FLAG'    => $pivcData['PIWC_CALL_FLAG'],
              'PIWC_MED_FLAG'     => $pivcData['PIWC_MED_FLAG'],
              'LATEST_CALL_DATE'  => $pivcData['LATEST_CALL_DATE'],
              'CALL_TIME'         => $pivcData['CALL_TIME'],
              'CUST_NAME'         => $pivcData['CUST_NAME'],
              'RESIDENCE_CONTACT' => $pivcData['RESIDENCE_CONTACT'],
              'OFFICE_CONTACT'    => $pivcData['OFFICE_CONTACT'],
              'MOBILE_NO'         => $pivcData['MOBILE_NO'],
              'PRECALLING_STATUS' => $pivcData['PRECALLING_STATUS'],
              'MAIN_REASON'       => $pivcData['MAIN_REASON'],
              'SUB_REASON'        => $pivcData['SUB_REASON'],
              'CALLING_REMARKS'   => $pivcData['CALLING_REMARKS'],
              'SOURCE'            => $pivcData['SOURCE'],
          ]
      ];

      $response = Http::withHeaders([
        'X-IBM-Client-Id'     => env('SBIL_PIWC_STATUS_APID_CLIENT'),
        'X-IBM-Client-Secret' => env('SBIL_PIWC_STATUS_APID_SECRET'),
        'Content-Type'        => 'application/json',
        'Accept'              => 'application/json',
        'ServicePassword'     => env('SBIL_PIWC_STATUS_SERVICE_PASSWORD'),
        'ServiceId'           => env('SBIL_PIWC_STATUS_SERVICE_ID'),
    ])->withOptions([
        'verify' => true // SSL Verification - use false only for local testing
    ])->post(env('SBIL_PIWC_STATUS_URL'), $params);

    $resArr = $response->json();

    if (!empty($resArr['UpdateGroupPIWCDetails_InstaResult'])) {
        if (strpos($resArr['UpdateGroupPIWCDetails_InstaResult'], 'SUCCESS') !== false) {
            Log::channel('daily')->info('RinnRaksha PIWC Status Update SUCCESS', [
                'params' => $params,
                'response' => $resArr,
            ]);
            $this->addLog('RinnRakshapivcStatusUpdate', json_encode($params), json_encode($resArr));
            return true;
        } else {
            Log::channel('daily')->error('RinnRaksha PIWC Status Update FAILED', [
                'params' => $params,
                'response' => $resArr,
            ]);
            $this->addLog('RinnRakshapivcStatusUpdate', json_encode($params), json_encode($resArr), 1);
        }
    } else {
        Log::channel('daily')->error('RinnRaksha PIWC Status Empty Response', [
            'params' => $params,
            'response' => $resArr,
        ]);
        $this->addLog('RinnRakshapivcStatusUpdate', json_encode($params), json_encode($resArr), 1);
    }

      return false;
  }
  public function updatePIVCStatusAPI(string $proposalNo, array $pivcData): bool
  {
      $params = [
          'Update_PIVC_STATUS' => [
              "PROPOSAL_NUMBR"        => $proposalNo,
              "VD_SUBMIT_DATE"        => $pivcData['VD_SUBMIT_DATE'] ?? null,
              "PIVC_CALL_FLAG"        => $pivcData['PIVC_CALL_FLAG'] ?? null,
              "PIVC_TYPE"             => $pivcData['PIVC_TYPE'] ?? null,
              "REMARKS"               => $pivcData['REMARKS'] ?? null,
              "FacialScorePercentage" => $pivcData['FacialScorePercentage'] ?? null,
              "VD_Verif"              => $pivcData['VD_Verif'] ?? null,
          ]
      ];

      $response = Http::withHeaders([
              'x-ibm-client-id'     => env('SBIL_STATUS_APIP_CLIENT'),
              'x-ibm-client-secret' => env('SBIL_STATUS_APIP_SECRET'),
              'Content-Type'        => 'application/json',
          ])
          ->withoutVerifying() // disables SSL verification, same as `$tt->ssl(FALSE)`
          ->post(env('SBIL_STATUS_PURL'), $params);

      $resArr = $response->json();

      $logTag = 'pivcStatusUpdate';

      if (!empty($resArr['Update_PIVC_STATUSResult'])) {
          if ($resArr['Update_PIVC_STATUSResult'] === 'SUCCESS') {
              $this->addLog($logTag, $params, $resArr);
              return true;
          } else {
              $this->addLog($logTag, $params, $resArr, true);
          }
      } else {
          Log::channel('daily')->error("KFD -- pivcStatusUpdate -- FAILED", ['params' => $params, 'response' => $resArr]);
          $this->addLog($logTag, $params, $resArr, true);
      }

      return false;
  }
  public function checkProposalNoExistDetailsPdf($proposal_no)
  {
    $result = Link::from('links as l')
    ->select('l.*', 'p.product_name', 'p.uin_no')
    ->join('products as p', 'p.id', '=', 'l.product_id')
    ->whereRaw('(l.proposal_no) = ?', [$proposal_no])
    // ->where('l.transcript_pdf_url', '!=', '')
    // ->orderByDesc('l.id')
    // ->orderByAsc('l.id')
    ->first();
// dd($result);
    return $result ? $result->toArray() : false;
  }

}







