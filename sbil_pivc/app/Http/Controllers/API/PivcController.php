<?php
namespace App\Http\Controllers\API;


use Carbon\Carbon;
use App\Models\Link;
use App\Models\Product;
// use App\Helpers\CommonHelper;
use App\Models\Sources;
use App\Helpers\XMLHelper;
use Jenssegers\Agent\Agent;
use App\Services\KfdService;

use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Services\LinkService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\CommonRepository;

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
           public function updateEditLinkResponse(Request $request)
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
        
        
     
                

          
            

        
       
          
        }


