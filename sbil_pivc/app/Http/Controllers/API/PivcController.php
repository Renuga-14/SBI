<?php
namespace App\Http\Controllers\API;


use Carbon\Carbon;
use App\Models\Link;
use App\Models\Product;
use App\Helpers\CommonHelper;
use App\Models\Sources;
use App\Helpers\XMLHelper;
use Jenssegers\Agent\Agent;
use App\Services\KfdService;

use Illuminate\Http\Request;

use App\Services\LinkService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\CommonRepository;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class PivcController extends Controller
{
    protected $linkService;
    protected $commonRepository;
    protected $KfdService;
   
    public function __construct(CommonRepository $commonRepository,LinkService $linkService,KfdService  $KfdService)
    {
        $this->commonRepository = $commonRepository;
        $this->linkService = $linkService;
        $this->KfdService = $KfdService;
    }
                       //create Rinnraksha link
    public function createRinnRakshaLink(Request $request)
    {
        Log::info('LOG:  rinn_raksha_request_log', [
            'POST' => request()->all(),
            'Headers' => request()->headers->all()
        ]);

        $rules = [
            'sbil_proposal_no' => 'required|alpha_num',
            'sbil_source'      => 'required',
            'sbil_uin_no'      => 'required|alpha_num',
            'sbil_data'        => 'required',
        ];

        // Custom error messages
        $messages = [
            'sbil_proposal_no.required' => 'Proposal Number is required.',
            'sbil_proposal_no.alpha_num' => 'Proposal Number must be alphanumeric.',
            'sbil_source.required' => 'Source is required.',
            'sbil_uin_no.required' => 'UIN Number is required.',
            'sbil_uin_no.alpha_num' => 'UIN Number must be alphanumeric.',
            'sbil_data.required' => 'Data is required.',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => 'Invalid Arguments. Please try again!',
                'errors' => $validator->errors(),
            ], 400);
        }


        $source = $request->input('sbil_source');
        $uin_no = $request->input('sbil_uin_no');
        $post_data = $request->all();

        $head_arr = [
            'short_name' => $request->header('client_name'),
            'ckey' => $request->header('client_key')
        ];

        // Trim headers
        $head_arr = array_map('trim', $head_arr);

      // Validate source
      $source_detail = Sources::validateSource($source);
      if (!$source_detail) {
          Log::info('LOG: invalid_source_access', ['POST' => $post_data]);
          return response()->json([
              'status' => false,
              'msg' => 'Invalid Source Access. Please try again!'
          ], 400);
      }

      // Validate UIN
      $uin_detail = Product::validateUin($uin_no, $source_detail['id']);
      if (!$uin_detail) {
          Log::info('LOG: invalid_uin_access', ['POST' => $post_data]);
          return response()->json([
              'status' => false,
              'msg' => 'Invalid Product Access. Please try again!'
          ], 400);
      }

      // Convert Proposal Number to uppercase
      $proposal_no = strtoupper($request->input('sbil_proposal_no'));

      // Parse XML data
      $xml_data = $request->input('sbil_data');
    //   $xmlDataArr = $pivcModel->parseRinnRikshaPIVCXml($xml_data);
      $xmlDataArr = XMLHelper::parseRinnRikshaPIVCXml($xml_data);

      if (!$xmlDataArr) {
          Log::info('LOG: invalid_xml_data', ['POST' => $post_data]);
          return response()->json([
              'status' => false,
              'msg' => 'Invalid data. Please try again!'
          ], 400);
      }

      // Check Proposal Number match
      $UpperCase_Proposal_no = strtoupper($xmlDataArr['PROPOSAL_NUMBER']);
      if ($UpperCase_Proposal_no !== $proposal_no) {
          Log::info('LOG: proposal number mismatch', ['POST' => $post_data]);
          return response()->json([
              'status' => false,
              'msg' => 'Proposal Number Mismatch. Please Check!'
          ], 400);
      }

      // Check Proposal Status

      $pivcStatusDetails = $this->linkService->checkProposalNoStatusCheck($proposal_no);

      if (isset($pivcStatusDetails['status']) && $pivcStatusDetails['status'] == 0) {
          if (in_array($pivcStatusDetails['source'], [2, 9, 10])) {
              return response()->json([
                  'status' => false,
                  'msg' => 'The Proposal Is Inactive'
              ], 400);
          }
      }
      // sms & email need to add

      $clientDetails = $this->linkService->checkClientExist($head_arr);  //print_r($clientDetails);die;
      $linkUid = $this->linkService->generateLinkUid();
      $linkUkey = $this->linkService->generateLinkKey();//print_r($linkUkey);die;


            $linkParams = 'uuid=' . $linkUid;
            $linkParamsHashed = Crypt::encryptString($linkParams);
            $hashUrl = config('constants.CLIENT_URL_RINN_DESIGN') . '?' . $linkParamsHashed;
            $shortUrl = $hashUrl;
            if ($tiny_url = $this->commonRepository->sbilShortUrl($hashUrl)) {
                $shortUrl = $tiny_url;
            }
            foreach ($xmlDataArr as $key => $value) {
                if (strpos($key, 'str_rinn_') === 0) {
                    if ($value == 'Y') {
                        $xmlDataArr[$key] = 'yes';
                    } elseif ($value == 'N') {
                        $xmlDataArr[$key] = 'no';
                    }
                }
            }
// need to add sms & email

                    $pivcDetails = $this->linkService->checkProposalData($proposal_no);
                    if (isset($pivcDetails) && $pivcDetails['complete_status'] == 0) {
                        $output_data = [
                            'proposal_no' => $proposal_no,
                            'source' => $source,
                            'short_url' => $pivcDetails['link_short'] ?? '',
                        ];

                        $this->linkService->addLog(
                            'rePIVCLinkFetch',
                            json_encode(['type' => "rePIVCLinkFetch : Proposal No - " . $request->input('proposal_no')]),
                            json_encode($output_data),
                            0
                        );



                        return response()->json([
                            'status' => true,
                            'msg' => 'Proposal link details fetched successfully!',
                            'output' => $output_data,
                        ]);
                    } else {




// print_r($proposalData);die;
            if (trim($xmlDataArr['LOAN_CATEGORY'])=='RINN_HL_SA_LESS_50L' || trim($xmlDataArr['LOAN_CATEGORY'])=='RINN_HL_SA_MORE_50L' || strtolower(str_replace(" ", "", $xmlDataArr['LOAN_CATEGORY']))=='housingloan' || strtolower(str_replace(" ", "", $xmlDataArr['LOAN_CATEGORY']))=='homeloan' || strtolower(str_replace(" ", "", $xmlDataArr['LOAN_CATEGORY']))=='homeequityloan') {
                $xmlDataArr['LOAN_CATEGORY'] = 'Home Loan';
            } else if (strtolower(str_replace(" ", "", $xmlDataArr['LOAN_CATEGORY']))=='personalloan' || strtolower(str_replace(" ", "", $xmlDataArr['LOAN_CATEGORY']))=='mortgageloan') {
                $xmlDataArr['LOAN_CATEGORY'] = 'Personal Loan';
            }
            $params_arr = array(
                'flow_key' => $uin_detail['product_slug'],
                'proposal_no' => $proposal_no,
                'source' => $source_detail['name'],
                'flow_data' => $xmlDataArr
            );

            $proposalNoCount =  $this->linkService->countProposalNo($proposal_no);

            $insertData = [
                'uid' => $linkUid,
                'ukey' => $linkUkey,
                'proposal_no' => $proposal_no,
                'source' => $source_detail['id'],
                'product_id' => $uin_detail['id'] ?? NULL,
                'params' => !empty($params_arr) ? json_encode($params_arr) : NULL,
                'expiry' => 45,
                'client_id' => $clientDetails[0]->id,
                'link' => $hashUrl,
                'link_short' => $shortUrl,
                'version' => ($proposalNoCount + 1),
            ];

            $linkId = $this->linkService->saveLink($insertData);

            if (!$linkId) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Error occurred while generating new link details. Please try later!',
                ], 500); // 500 - Internal Server Error
            }
// sms & email
            // Log the request data and insertion result
            Log::channel('KFD_LOG')->info('KFD -- generate_url', [
                'POST' => request()->all(),
                'OutPut' => $insertData,
                'Insert ID' => $linkId
            ]);
            if ($linkId) {
                $output_data = [
                    'proposal_no' => $proposal_no,
                    'source' => $source,
                    'short_url' => $shortUrl
                ];

                // Log the generated link
                $this->linkService->addLog(
                    'genPIVCLinkURL',
                    json_encode(['type' => "genPIVCLinkURL : Proposal No - " . $request->input('proposal_no')]),
                    json_encode($output_data),
                    0
                );

                return response()->json([
                    'status' => true,
                    'msg' => 'New proposal link details added successfully!',
                    'output' => $output_data
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'msg' => 'Error occurred while generating new link details. Please try later!'
                ], 500);
            }

        //  print_r( $shortUrl);

    }
}


                       //get Rinnraksha short link
    public function getRinnRakshaLink(Request $request)
    {
        $proposal_no = $request->input('proposal_no');


            $pivcStatusDetails = $this->linkService->checkProposalNoStatusCheck($proposal_no);
            $params = json_decode($pivcStatusDetails['params'], true);
            $slug = $params['flow_key'];
            $data = $params['flow_data'];
            $uin_no = $params['flow_data']['UIN'] ?? null;
            $source_id = $pivcStatusDetails['source'];
            $complete_status = $pivcStatusDetails['complete_status'];
            $source = $params['source'] ?? null;
            $completed_on = $pivcStatusDetails['completed_on'] ?? null;
            $dob = $params['flow_data']['DATE_OF_BIRTH'] ?? null;

            if (!empty($params['flow_data']['PLAN'])) {
                if (in_array($params['flow_key'], [
                    'sbilm_smart_annuity_plus',
                    'sbilsa_smart_annuity_plus',
                    'sbilpl_smart_annuity_plus'
                ])) {
                    $slug = $params['flow_key'] . ' ' . $params['flow_data']['PLAN'];
                }
            }

            // Video Calling
            if ($complete_status == 0) {

                echo "Short link is: " . $pivcStatusDetails['link_short'];

            } else {
                echo "No video call code ";
            }


        }
                            //validate RinnRiksha Link
        public function validateRinnRikshaLink(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'sbil_pivc_url' => 'required|url',
            ]);

            if ($validator->fails()) {

                return response()->json(['status' => false, 'msg' => 'Invalid PIVC link!'], 400);
            }

            if (!CommonHelper::checkPostParamNull($request, ['sbil_pivc_url'])) {
                return response()->json(['status' => false, 'msg' => 'PIVC URL is required!'], 400);
            }

            $pivc_url = $request->input('sbil_pivc_url');
            $uuid = $this->linkService->getUrlUuid($pivc_url);

            if (!$uuid) {

                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid RinnRaksha PIVC link111!'
                ], 400);
            }

            $httpXRequestedWith = request()->header('HTTP_X_REQUESTED_WITH', null);
            $appViewStatus = !empty($httpXRequestedWith) ? CommonHelper::validateMobApp($httpXRequestedWith) : false;

            $checkPIVCLink = $this->linkService->checkPivcUidExist($uuid);



            if (!$checkPIVCLink) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid RinnRaksha PIVC link2//!'
                ]);
            }

            $pivcLinkDetails = $this->linkService->getPIVCLinkDetail($checkPIVCLink->id);

            if (!$pivcLinkDetails) {
                return response()->json([
                    'status' => false,
                    'msg' => 'PIVC link details not found!'
                ]);
            }

            $flowKeyValue = "";
            $outputData = [
                'param' => json_decode($pivcLinkDetails->params, true),
                'link' => $pivcLinkDetails->link,
                'url' => $pivcLinkDetails->link_short,
                'lkey' => $pivcLinkDetails->ukey,
                'app_view' => $appViewStatus
            ];

            $flowKeyValue .= $outputData['param']['flow_key'] ?? '';

            if ($pivcLinkDetails->product_id) {
                $prodDetail = $this->linkService->validateProduct($pivcLinkDetails->product_id, $pivcLinkDetails->source);

                if ($prodDetail === false) {
                    Log::channel('KFD_LOG')->error('KFD -- validate_rinn_raksha_product --- Fail!');
                    return response()->json(['status' => false, 'msg' => 'Invalid Product Link Access. Please try again!']);
                }
            }

            $expiryDays = check_value_is_null($pivcLinkDetails->expiry) ? $pivcLinkDetails->expiry : null;
            $completeStatus = !empty($pivcLinkDetails->complete_status);
            $disagreementStatus = (bool) $pivcLinkDetails->disagree_status;

            // Videocalling
            $fullProductName = $prodDetail['product_name'];
            $version = $pivcLinkDetails->version;

            // Expiry Check
            if ($expiryDays !== null) {

                $createdOn = $pivcLinkDetails->created_on ? Carbon::parse($pivcLinkDetails->created_on) : null;
                $curDate = $pivcLinkDetails->cur_date ? Carbon::parse($pivcLinkDetails->cur_date) : null;

                $expiryDate = $createdOn ? $createdOn->addDays($expiryDays)->format('Ymd') : null;
                $curDate = $curDate ? $curDate->format('Ymd') : null;



                if (($expiryDate - $curDate) <= 0) {
                    return response()->json([
                        'status' => true,
                        'expired' => true,
                        'completed' => $completeStatus,
                        'disagree_status' => $disagreementStatus,
                        'msg' => 'Given Rinn Raksha PIVC URL is expired!',
                        'output' => $outputData,
                        'full_product_name' => $fullProductName,
                        'version' => $version
                    ]);
                }


                if (strpos($flowKeyValue, '_rinn_raksha') !== false) {
                    $finalJson = "https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/rinraksha/product_manifest.json";

                    return response()->json([
                        'status' => true,
                        'expired' => false,
                        'completed' => $completeStatus,
                        'disagree_status' => $disagreementStatus,
                        'full_product_name' => $fullProductName,
                        'version' => $version,
                        'msg' => 'Given Rinn Raksha PIVC URL is valid!',
                        'output' => $outputData,
                        'json_name' => $finalJson
                    ]);
                }
            }

            $response = [
                'status' => true,
                'expired' => false,
                'completed' => $completeStatus,
                'disagree_status' => $disagreementStatus,
                'full_product_name' => $fullProductName,
                'version' => $version,
                'msg' => 'Given Rinn Raksha PIVC URL is validated!',
                'output' => $outputData
            ];




            return response()->json($response);

        }



        public function getGeoLocationAddress(Request $request)
        {


            $validator = Validator::make($request->all(), [
                'sbil_key' => 'required',
                'sbil_geo_lat' => 'required',
                'sbil_geo_long' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Please supply all the required values. Please try later!',
                    'errors' => $validator->errors(),
                ]);
            }


            $linkKey = trim($request->input('sbil_key', ''));
            $geoLat = $request->input('sbil_geo_lat', 0);
            $geoLong = $request->input('sbil_geo_long', 0);

            if ($geoLat == 0 || $geoLong == 0) {
                Log::info('KFD -- get_geoLocationAddress --- POST : ', $request->all());
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid Geo Co-ordinates. Please try again!',
                ]);
            }

            if (!empty($linkKey)) {
                $linkDetails =$this->linkService->checkLinkKeyExist($linkKey);

                if ($linkDetails) {
                    $geoLocAddress =$this->linkService->getGeoAddress($geoLat, $geoLong);
                    Log::info('KFD -- get_geo_address --- POST : ', $request->all());

                    if (!empty($geoLocAddress)) {
                        return response()->json([
                            'status' => true,
                            'msg' => 'Fetch the geo location address details successfully!',
                            'output' => [
                                'latitude' => $geoLat,
                                'longitude' => $geoLong,
                                'address' => $geoLocAddress,
                            ],
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'msg' => 'Error occurred while fetching the geo address details. Please try later!',
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Given Link is not valid!',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given Link is not valid!',
                ]);
            }
        }

        public function deviceDetails(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'sbil_key' => 'required',
                'sbil_device' => 'required',
            ]);
          
        
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Please supply all the required values. Please try later!',
                ]);
            }
           
            $link_key = trim($request->input('sbil_key', ''));
            $device = $request->input('sbil_device', '');
           
            if (empty($link_key)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given Link is not valid!',
                ]);
            }
           
            $browser = request()->header('User-Agent'); // Get user agent details
        
            $agent = new Agent();

            $deviceDetails = [
                'device' => $device,
                'browser' => $agent->browser(), // Get browser name
                'platform' => $agent->platform(), // Get OS platform
                'device_type' => $agent->device(), // Get device type
            ];
           
            $link_details =$this->linkService->checkLinkKeyExist($link_key);
          
            if ($link_details) {
                $link_id = $link_details['id'];
               
              
              
                $geo_loc_address = $this->linkService->setDeviceDetails($link_id, $deviceDetails);

                Log::info('KFD_LOG: KFD -- DeviceDetails --- POST : ' . json_encode($request->all()) . 
                    ' --- OutPut : Success --- Geo Location Address: ' . json_encode($geo_loc_address));
                
        
                return response()->json([
                    'status' => true,
                    'msg' => 'Updated device details!',
                    'browser' => $deviceDetails,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given Link is not valid!',
                ]);
            }
        }

        public function updateLinkResponse(Request $request)
        {
            $validatedData = $request->validate([
                'sbil_key' => 'required',
                'sbil_ckey' => 'required|in:cPerDet,cPolDet,cMedQuest,cBenIll,cProdBenef,cSmsOtp,eMedQuest,eSmsOtp',
                'sbil_cpage' => 'required',
                'sbil_castatus' => 'nullable'
            ]);

            $link_key = trim($validatedData['sbil_key']);
            $link_configKey = $validatedData['sbil_ckey'];
            $page = $validatedData['sbil_cpage'];

            if ($page === 'Personal Details') {
                $this->linkService->clearResponseNull($link_key);
            }

            $link_details =  $this->linkService->checkLinkKeyExist($link_key);


            if (!$link_details) {
                return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
            }

            $link_id =$link_details['id'];

            $configParams = [
                'page' => $page,
                'agree_status' => $request->boolean('sbil_castatus', false),
                'created_on' =>now()->toDateTimeString()
            ];
            $config_response = $this->linkService->updateLinkResponseNew($link_id, $link_configKey, $configParams);
         //   print_r( $config_response);die;
            if ($config_response) {
                return response()->json(['status' => true, 'msg' => 'Updated the link Response!']);
            } else {
                return response()->json(['status' => false, 'msg' => 'Link response is not updated!']);
            }


           }
           public function updateEditLinkResponse(Request $request) {
                // 1. Validate required fields
    $validator = Validator::make($request->all(), [
        'sbil_key'      => 'required',
        'sbil_ekey'     => 'required|in:ePerDet,ePolDet,eMedQuest,eProdBenef,eBenIll',
        'sbil_epage'    => 'required|string',
        'sbil_edata'    => 'required|string' // raw JSON string
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'msg'    => 'Please supply all the required values. Please try later!',
            'errors' => $validator->errors()
        ]);
    }

    // 2. Convert JSON string to array
    $sbil_edata_raw = $request->input('sbil_edata');
    $sbil_edata = json_decode($sbil_edata_raw, true);
  
    if (!is_array($sbil_edata)) {
        return response()->json([
            'status' => false,
            'msg'    => 'Invalid JSON format in sbil_edata'
        ]);
    }

    $fields_to_check = [
        'in_name',
        'in_gender',
        'in_occupation',
        'in_nominee_name',
        'in_nominee_relation',
        'in_mobile_no',
        'in_address',
        'in_address1',
        'in_address2',
        'in_address3'
    ];

    $alpha_numeric = true;

    foreach ($fields_to_check as $field) {
        if (isset($sbil_edata[$field]) && !$this->alphaNumericCheck($sbil_edata[$field])) {
            $alpha_numeric = false;
            break;
        }
    }

    if (!$alpha_numeric) {
        return response()->json(['status' => false, 'msg' => 'Invalid Arguments. Please try again!']);
    }
    // print_r( $fields_to_check);die;
    // 4. Everything passed: proceed with logic
    $link_key       = $request->input('sbil_key');
    $link_configKey = $request->input('sbil_ekey');
    $post_data      = $request->all(); // all request data
 
    // Do something with $link_key, $link_configKey, $post_data...

  
    if (!empty($link_key)) {
        $link_details = $this->linkService->checkLinkKeyExist($link_key);
    if ($link_details) {
        $link_id = $link_details['id'];

        $configParams = [
            'page'       => $request->input('sbil_epage') ?? null,
            'input'      => $request->filled('sbil_edata') ? json_decode($request->input('sbil_edata'), true) : null,
            'created_on' => Carbon::now()->toDateTimeString(),
        ];

        $this->linkService->setDisAgreeStatus($link_id);
        $config_response = $this->linkService->updateLinkResponse($link_id, $link_configKey, $configParams);

        if ($config_response === true) {
            return response()->json(['status' => true, 'msg' => 'Updated the link Response!']);
        } else {
            return response()->json(['status' => false, 'msg' => 'Link response is not updated!']);
        }
    } else {
        return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
    }
}
return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
   
           }
      /*      public function updateEditLinkResponse(Request $request)
           {
                 // Validate input data
               $validatedData = $request->validate([
                   'sbil_key'   => 'required',
                   'sbil_ekey'  => 'required|in:ePerDet,ePolDet,eMedQuest,eProdBenef,eBenIll',
                   'sbil_epage' => 'required',
                   'sbil_edata' => 'nullable'
               ]);

               $link_key = trim($validatedData['sbil_key']);
               $link_configKey = $validatedData['sbil_ekey'];

               $link_key = trim($validatedData['sbil_key']);
               $link_configKey = $validatedData['sbil_ekey'];
               $sbil_edata = $request->input('sbil_edata') ? json_decode($request->input('sbil_edata'), true) : null;
             
               // Validate alphanumeric fields
               $fieldsToCheck = [
                   'in_name', 'in_gender', 'in_occupation', 'in_nominee_name',
                   'in_nominee_relation', 'in_mobile_no', 'in_address',
                   'in_address1', 'in_address2', 'in_address3'
               ];

             
               foreach ($fieldsToCheck as $field) {

                   if (!isset($sbil_edata[$field]) ) {

                       return response()->json(['status' => false, 'msg' => 'Invalid Arguments. Please try again!'], 400);
                   }
               }//!isset($sbil_edata[$field]) ||!self::alphaNumericCheck($sbil_edata[$field])


               if (!empty($link_key)) {
                   $link_details = $this->linkService->checkLinkKeyExist($link_key);

                   if ($link_details) {
                       $link_id = $link_details['id'];

                       $configParams = [
                           'page'       => $validatedData['sbil_epage'],
                           'input'      => $sbil_edata,
                           'created_on' => now()->toDateTimeString()
                       ];

                       $this->linkService->setDisAgreeStatus($link_id);

                       $config_response = $this->linkService->updateLinkResponse($link_id, $link_configKey, $configParams);

                       if ($config_response) {
                           return response()->json(['status' => true, 'msg' => 'Updated the link Response!']);
                       } else {
                           return response()->json(['status' => false, 'msg' => 'Link response is not updated!']);
                       }
                   } else {
                       return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
                   }
               }

               return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
           } */

           public function alphaNumericCheck($input)
           {
               return preg_match('/^[a-zA-Z0-9 ]*$/', $input);
           }
           

           public function rinnRikshaQuestions(Request $request) {
            $reqParams = ['sbil_key', 'sbil_cpage', 'sbil_data'];
        
            $sbilCpage = $request->input('sbil_cpage', '');
            
            if (in_array(null, array_map([$request, 'input'], $reqParams), true)) {
                return response()->json(['status' => false, 'msg' => 'Invalid Arguments. Please try again!']);
            }
            
            if (in_array($sbilCpage, ['Medical Confirmation Screen One', 'Medical Confirmation Screen Two'])) {
                $linkKey = trim($request->input('sbil_key', ''));
                $linkConfigKey = $request->input('sbil_key', '');
                
                if (!empty($linkKey)) {
                    $linkDetails = $this->linkService->checkLinkKeyExist($linkKey);
                    
                    if (!$linkDetails) {
                        return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
                    }
                    
                    $linkId = $linkDetails['id'];
                    $configParams = [
                        'page' => $request->input('sbil_cpage', null),
                        'input' => optional(json_decode($request->input('sbil_data', '{}'), true))['sbil_data'] ?? null,
                        'created_on' => now(),
                    ];
                    
                    $configResponse = $this->linkService->updateLinkResponseRinnRiksha($linkId, $linkConfigKey, $configParams);

                    return response()->json([
                        'status' => $configResponse === true,
                        'msg' => $configResponse === true ? 'Updated the Links Response!' : 'Link response is not updated!'
                    ]);
                }
                return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
            }
            
            return response()->json(['status' => false, 'msg' => 'Invalid Page.']);
        }
        public function getAllImages(Request $request)
        {
            $req_params = ['sbil_key'];

            // Check if the required parameter is present
            if ($request->has('sbil_key')) {
                $validator = Validator::make($request->all(), [
                    'sbil_key' => 'required|alpha_num'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Please supply all the required values. Please try later!'
                    ]);
                }

                $link_key = trim($request->input('sbil_key', ''));

                if ($link_key !== '') {
                    $linkDetails = $this->linkService->checkLinkKeyExist($linkKey);

                    if ($link_details && !empty($link_details['consent_image_url'])) {
                        $consent = json_decode($link_details['consent_image_url'], true) ?? [];
                    } else {
                        $consent = [];
                    }

                    if ($link_details && !empty($link_details['reg_photo_url'])) {
                        $reg = json_decode($link_details['reg_photo_url'], true) ?? [];
                    } else {
                        $reg = [];
                    }

                    // Merge consent and reg arrays
                    $all = array_merge($consent, $reg);
                    $final_arr = [];

                    foreach ($all as $row) {
                        unset($row['latitude'], $row['longitude'], $row['location'], $row['language']);
                        $final_arr[] = $row;
                    }

                    if (!empty($final_arr)) {
                        return response()->json([
                            'status' => true,
                            'image_load' => $final_arr
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'msg' => 'No images found!'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Given Link is not valid!'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'msg' => 'Invalid Arguments. Please try again!'
                ]);
            }
        }
        
        
        
        public function updateCompleteStatus(Request $request)
        {
            // Define the validation rules
            $validator = Validator::make($request->all(), [
                'sbil_key' => 'required',  // sbil_key should be alphanumeric
                'sbil_cstatus' => 'required',        // sbil_cstatus should be required
            ]);

            // If validation fails, return a response for missing or invalid arguments
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'msg' => $request->has('sbil_key') && $request->has('sbil_cstatus') 
                                ? 'Please supply all the required values. Please try later!' 
                                : 'Invalid Arguments. Please try again!'
                ], $request->has('sbil_key') && $request->has('sbil_cstatus') ? 422 : 400); // 422 Unprocessable Entity or 400 Bad Request
            }

            $link_key = trim($request->input('sbil_key'));
            if (empty($link_key)) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given Link is not valid!'
                ]);
            }

            $link_status = $request->input('sbil_cstatus') ? 1 : 0;
            // $linkKeyDetails = Pivc::checkLinkKeyExist($link_key);
            $linkKeyDetails = $this->KfdService->checkLinkKeyExist($link_key);
            if (!$linkKeyDetails) {
                return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
            }
            $link_id = $linkKeyDetails['id'];
                // Video calling

                // $params = json_decode($linkKeyDetails->params ?? '{}', true);
                $params = json_decode($linkKeyDetails['params'], true);
                $slug = $params['flow_key'] ?? '';
                $proposal_no = $params['proposal_no'] ?? '';
           
           
            if (
                isset($params['flow_key']) &&
                strpos($params['flow_key'], '_smart_annuity_plus') !== false &&
                isset($params['flow_data']['PLAN']) &&
                $params['flow_data']['PLAN'] !== ''
            ) {
                $slug = $params['flow_key'] . ' ' . $params['flow_data']['PLAN'];
            }
            $_product_array = array(
                'sbilm_retire_smart_plus', 'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus', 
                'sbilo_retire_smart_plus', 'sbilm_retire_smart', 'sbilsa_retire_smart', 
                'sbilpl_retire_smart', 'sbilo_retire_smart', 'sbilo_smart_annuity_plus 1.2', 
                'sbilm_smart_annuity_plus 1.2', 'sbilsa_smart_annuity_plus 1.2', 'sbilpl_smart_annuity_plus 1.2','sbilo_smart_annuity_plus 1.3', 
                'sbilm_smart_annuity_plus 1.3', 'sbilsa_smart_annuity_plus 1.3', 'sbilpl_smart_annuity_plus 1.3',
                'sbilo_smart_annuity_plus 1.10', 
                'sbilm_smart_annuity_plus 1.10', 'sbilsa_smart_annuity_plus 1.10', 'sbilpl_smart_annuity_plus 1.10',
                'sbilo_smart_annuity_plus 2.2', 
                'sbilm_smart_annuity_plus 2.2', 'sbilsa_smart_annuity_plus 2.2', 'sbilpl_smart_annuity_plus 2.2',
                'sbilo_smart_annuity_plus 2.3', 
                'sbilm_smart_annuity_plus 2.3', 'sbilsa_smart_annuity_plus 2.3', 'sbilpl_smart_annuity_plus 2.3',
                'sbilm_smart_platina_plus','sbilpl_smart_platina_plus','sbilsa_smart_platina_plus'
            );
            

            if (in_array($slug, $_product_array)) {
                $pivcCompleteStatus = $this->linkService->setCompleteStatus($link_id);
                // $this->callJobTranscript($proposal_no);
            } else {
                $pivcCompleteStatus = $this->linkService->setCompleteStatus($link_id);
            }
            if (!$pivcCompleteStatus) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given Link is not Completed!',
                    'data' => $pivcCompleteStatus // Optional: for debugging
                ]);
            }
            $link_details = $this->linkService->getPIVCLinkDetail($link_id); 
            $link_details = (array) $link_details;

            if (in_array($link_details['source'], [2, 3, 5, 6, 8, 9, 10]) && $pivcCompleteStatus) {
                $params_arr = json_decode($link_details['params'], true);
                // $mobile_no = check_had_value($params_arr['flow_data']['MOBILE_NUMBER']);
            
              /*   if (Str::contains($link_details['link'], 'adc')) {
                    $responseArr = json_decode($link_details['response'], true);
                    if ($mobile_no && (pivcRemarks(1, $responseArr) !== 'Mismatch')) {
                        $smsMsg = smsLinkCompleteMsgTemplate($link_details['proposal_no'], 
                            in_array($link_details['source'], [2, 5, 6, 9, 10]) ? config('constants.SBIL_MCONNECT_TFN') : null
                        );
            
                        app(\App\Services\PivcService::class)->sendSms($mobile_no, $smsMsg);
                    }
                } */
            
                $resArr = json_decode($link_details['response'], true);
                $paramsdata = json_decode($link_details['params'], true);
              
                if (isset($paramsdata['flow_key']) && Str::contains(strtolower($paramsdata['flow_key']), '_rinn_raksha')) {
                    $statusremark =  CommonHelper::pivcRinnRakshaRemarks(1, $link_details['disagree_status'], $resArr, now()->format('m/d/Y H:i'));
                  
                    $statusData = [
                        'FORM_NUM'           => $paramsdata['flow_data']['FORMNUMBER'],
                        'PL_POL_NUM'         => $paramsdata['flow_data']['RD_MASTER_POLICY_NO'],
                        'LOAN_ACCT_NUM'      => $paramsdata['flow_data']['LOANACCOUNTNUMBER'],
                        'LOAN_PLUS_ACCT_NUM' => $paramsdata['flow_data']['LOANACCOUNTNUMBER'],
                        'PIWC_CALL_FLAG'     => $statusremark['piwc_call_flag'],
                        'PIWC_MED_FLAG'      => $statusremark['piwc_med_flag'],
                        'LATEST_CALL_DATE'   => now()->format('m/d/Y'),
                        'CALL_TIME'          => now()->format('H:i'),
                        'CUST_NAME'          => $paramsdata['flow_data']['POLICY_HOLDER_NAME'],
                        'RESIDENCE_CONTACT'  => "0",
                        'OFFICE_CONTACT'     => "0",
                        'MOBILE_NO'          => $paramsdata['flow_data']['MOBILE_NUMBER'],
                        'PRECALLING_STATUS'  => $statusremark['precalling'],
                        'MAIN_REASON'        => $statusremark['mainreason'],
                        'SUB_REASON'         => $statusremark['sub_reason'],
                        'CALLING_REMARKS'    => $statusremark['remarks'],
                        'SOURCE'             => "Anoor",
                    ];
                    $this->linkService->updateGroupPIWCDetailsInsta($statusData);
                    $REMARKS = $statusData['MAIN_REASON'];
            
                } else {
                    $product_array = [155];
                    $allowedStatusType = [
                        2 => 'MCNCT',
                        3 => 'ONLINE',
                        5 => 'SMTADV',
                        6 => 'PHYSICAL'
                    ];
            
                    $pivcStatus = CommonHelper::pivcFullRemarkStatus(1, $link_details['disagree_status'], $resArr);
                    $productCheck = empty($link_details['response']) && in_array($link_details['product_id'], $product_array);
            
                    if ($pivcStatus === 'Y' || $productCheck) {
                        $vd_verif = 'N';
                    } elseif ($pivcStatus === 'N') {
                        $vd_verif = 'D';
                    } else {
                        $vd_verif = 'N';
                    }
            
                    $PIVC_CALL_FLAG = $productCheck ? 'Y' : $pivcStatus;
                    $REMARKS = $productCheck ? 'Clear Case' : (CommonHelper::pivcFullRemarks(1, $link_details['disagree_status'], $resArr));
            
                    $statusData = [
                        'VD_SUBMIT_DATE'       => now()->format('m/d/Y'),
                        'PIVC_CALL_FLAG'       => $PIVC_CALL_FLAG,
                        'PIVC_TYPE'            => $allowedStatusType[$link_details['source']] ?? null,
                        'REMARKS'              => $REMARKS,
                        'FacialScorePercentage'=> 0,
                        'VD_Verif'             => $vd_verif,
                    ];
                    $this->linkService->updatePIVCStatusAPI($link_details['proposal_no'], $statusData);
           
                    $REMARKS = $statusData['REMARKS'];
                }
                return response()->json([
                    'status'       => true,
                    'completed_on' => $link_details['completed_on'] ?? null,
                    'pivc_remarks' => $REMARKS ?? null,
                    'msg'          => 'Updated the link status!'
                ]);
            }
            

        }

        public function callJobTranscript($proposal_no)
        {
            $url = 'https://pivc.sbilife.co.in/portal/cron/job/generateTranscriptPDF_VideoCalling/' . $proposal_no;
          //  echo $url;die;
           // echo 'https://pivc.sbilife.co.in/portal/cron/job/generateTranscriptPDF_VideoCalling/' . $proposal_no;die;
            $curl = curl_init();
    
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                )
            );
    
            $response = curl_exec($curl);
           
            curl_close($curl);
          // print_r($response. "1");exit;
            return $response;
        }
    
                

          
            

        
       
          
        }


