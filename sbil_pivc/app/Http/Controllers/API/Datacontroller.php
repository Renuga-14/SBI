<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Kfd;
use App\Models\DataModel;
use App\Models\CommonModel;
use App\Http\Controllers\Controller;
use App\Services\KfdService;
use App\Services\DataService;
use App\Models\Link;
// use App\Services\ImageService;
use App\Helpers\CommonHelper;


use Illuminate\Support\Facades\Validator;
class DataController extends Controller
{
    protected $KfdService;
    public function __construct(KfdService  $KfdService)
    {
/*         $this->commonRepository = $commonRepository;
        $this->linkService = $linkService; */
        $this->KfdService = $KfdService;
    }
    public function addConsentImage(Request $request)
    {

        $rules = [
            'sbil_key' => 'required|string',
            'sbil_consent_img'      => 'required',
        ];

        // Custom error messages
        $messages = [
            'sbil_key.required' => 'SBI KEY is required.',
            // 'sbil_key.string' => 'SBI KEY must be alphanumeric.',
            'sbil_consent_img.required' => 'Image is required.',
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


        $linkKey = trim($request->input('sbil_key'));
        $linkImg = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $request->input('sbil_consent_img'));
        $linkImgData = base64_decode($linkImg);
        $linkMediaAppend = ($request->input('sbil_media_append')=='true')? TRUE : FALSE;

        $metaDetails = [
            'lat' => $request->input('sbil_lat', 0),
            'long' => $request->input('sbil_long', 0),
            'loc' => $request->input('sbil_loc', ''),
            'lang' => $request->input('sbil_lang', ''),
            'scrn' => $request->input('sbil_scrn', '')
        ];


        if (!empty($linkKey) && $linkImgData !== false) {

            $linkDetail = $this->KfdService->checkLinkKeyExist($linkKey);
          if($linkDetail!==false)
            {
                $link_id = $linkDetail['id'];
                $fileNameDetails = app(DataService::class)->consentImageFile($link_id,$metaDetails['scrn']);

                if (!$fileNameDetails['status']) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Given file data is invalid!'
                    ], 400); // 400 Bad Request
                } else {
                    $file_name_data = array(
                        'file_name'=>$fileNameDetails['name'],
                        'product_name'=>$fileNameDetails['p_name'],
                        'file_loc'=>config('constants.DF_ADC_CONSENT_IMG_PATH')
                    );

                    $imageDetails = app(DataService::class)->addConsentJPEGImageFile($file_name_data,$linkImgData,$metaDetails); //print_r($imageDetails);die;
                    if ($imageDetails['status']) {
                        $infoParam = [
                            'latitude'  => $metaDetails['lat'],
                            'longitude' => $metaDetails['long'],
                            'location'  => $metaDetails['loc'],
                            'screen'    => $metaDetails['scrn'],
                            'language'  => $metaDetails['lang']
                        ];

                        if (config('app.env') === 'local') {
                            $regImgName = $imageDetails['url'];
                            // $regImgName = $imageDetails['name'];
                        } else {
                            // Delete the local file after upload
                            CommonHelper::localFileDelete($imageDetails['path']);
                            $regImgName = $imageDetails['url'];
                        }
                        $linkMediaUrlUpdate = app(DataService::class)->updateConsentPhotoUrl($link_id,$regImgName,$linkMediaAppend,$infoParam);

                        return response()->json(['status' => true, 'msg' => 'Successfully added the captured user image!']);
                    } else {
                        return response()->json(['status' => false, 'msg' => 'Error occurred while creating the image!']);
                    }

                }
            } else  {
                return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
            }

        } else
        {
            return response()-> json(array('status'=>FALSE,'msg'=>'Given Link or data is not valid!'));
        }
    }

    public function addCapturedScreenShot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sbil_key' => 'required',
            'sbil_screen_img' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'msg' => 'Please supply all the required values.']);
        }

        $linkKey = trim($request->input('sbil_key'));
        $linkImg = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $request->input('sbil_screen_img'));
        $linkImgData = base64_decode($linkImg);

        $linkMediaAppend = $request->input('sbil_media_append') === 'true';


        $metaDetails = [
            'lat' => $request->input('sbil_lat', 0),
            'long' => $request->input('sbil_long', 0),
            'loc' => $request->input('sbil_loc', ''),
            'lang' => $request->input('sbil_lang', ''),
            'scrn' => $request->input('sbil_scrn', ''),
        ];

        if (!$linkImgData) {
            return response()->json(['status' => false, 'msg' => 'Invalid image data!']);
        }

        if (!empty($linkKey) && $linkImgData !== false) {
            $linkDetail = $this->KfdService->checkLinkKeyExist($linkKey);
            if($linkDetail!==false)
              {
                $link_id = $linkDetail['id'];
                $fileNameDetails = app(DataService::class)->captureImageScreenShotFile($link_id,$metaDetails['scrn']);
                if (!$fileNameDetails['status']) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'Given file data is invalid!'
                    ], 400); // 400 Bad Request
                } else {
                    $file_name_data = array(
                        'file_name'=>$fileNameDetails['name'],
                        'product_name'=>$fileNameDetails['p_name'],
                        'file_loc'=>config('constants.DF_ADC_CAPTURE_IMG_PATH')
                    );
                    $imageDetails = app(DataService::class)->addCapturedJPEGImageFile($file_name_data,$linkImgData,$metaDetails);//print_r($imageDetails);die;
                    if ($imageDetails['status']) {
                        $infoParam = [
                            'latitude'  => $metaDetails['lat'],
                            'longitude' => $metaDetails['long'],
                            'location'  => $metaDetails['loc'],
                            'screen'    => $metaDetails['scrn'],
                            'language'  => $metaDetails['lang']
                        ];

                        if (config('app.env') === 'local') {
                            $regImgName = $imageDetails['url'];
                            // $regImgName = $imageDetails['name'];
                        } else {
                            CommonHelper::localFileDelete($imageDetails['path']);
                            $regImgName = $imageDetails['url']; // Use S3 URL
                        }

                        // Update screenshot URL in database
                        $linkMediaUrlUpdate = app(DataService::class)->updateScreenShotPhotoUrl($link_id,$regImgName,$linkMediaAppend,$infoParam);

                        return response()->json([
                            'status' => true,
                            'msg'    => 'Successfully added the captured user image!'
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'msg'    => 'Error occurred while creating the image!'
                        ]);
                    }


                }

              } else {
                return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
              }
        } else
        {
            return response()-> json(array('status'=>FALSE,'msg'=>'Given Link or data is not valid!'));
        }

    }

public function addCapturedImage(Request $request)
{
    $validator = Validator::make($request->all(), [
        'sbil_key' => 'required',
        'sbil_reg_img' => 'required',

    ]);
    if ($validator->fails()) {
        return response()->json(['status' => false, 'msg' => 'Please supply all the required values. Please try later!']);
    }

    $validatedData = $validator->validated();
    $linkKey = trim($validatedData['sbil_key']);
    $linkImg = str_replace('data:image/jpeg;base64,', '', $validatedData['sbil_reg_img']);
    $linkImg = str_replace(' ', '+', $linkImg);
    $linkImgData = base64_decode($linkImg);
    $linkMediaAppend = ($request->input('sbil_media_append')=='true')? TRUE : FALSE;
    if (!$linkImgData) {
        return response()->json(['status' => false, 'msg' => 'Invalid image data!']);
    }

    $metaDetails = [
        'lat'  => $request->input('sbil_lat', 0),
        'long' => $request->input('sbil_long', 0),
        'loc'  => $request->input('sbil_loc', ''),
        'lang' => $request->input('sbil_lang', ''),
        'scrn' => $request->input('sbil_scrn', ''),
    ];

// print_r($metaDetails);die;
    if (!empty($linkKey) && $linkImgData !== false) {
        $linkDetail = $this->KfdService->checkLinkKeyExist($linkKey);
        if (!$linkDetail) {
            return response()->json(['status' => false, 'msg' => 'Given Link is not valid!']);
        } else {
            $link_id = $linkDetail['id'];
            $fileNameDetails = app(DataService::class)->captureImageFile($link_id,$metaDetails['scrn']);

            if (!$fileNameDetails['status']) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Given file data is invalid!'
                ], 400); // 400 Bad Request
            } else {
                $file_name_data = array(
                    'file_name'=>$fileNameDetails['name'],
                    'product_name'=>$fileNameDetails['p_name'],
                    'file_loc'=>config('constants.DF_ADC_CAPTURE_IMG_PATH')
                );

                $imageDetails = app(DataService::class)->addCapturedJPEGImageFile($file_name_data,$linkImgData,$metaDetails);
                if ($imageDetails['status']) {
                    $infoParam = [
                        'latitude'  => $metaDetails['lat'],
                        'longitude' => $metaDetails['long'],
                        'location'  => $metaDetails['loc'],
                        'screen'    => $metaDetails['scrn'],
                        'language'  => $metaDetails['lang']
                    ];
                    // print_r($infoParam);die;
                    if (config('app.env') === 'local') {
                        $regImgName = $imageDetails['url'];
                    } else {
                        // Delete the local file after upload
                        CommonHelper::localFileDelete($imageDetails['path']);
                        $regImgName = $imageDetails['url'];
                    }

                    $linkMediaUrlUpdate = app(DataService::class)->updateRegPhotoUrl($link_id,$regImgName,$linkMediaAppend,$infoParam);

                    return response()->json(['status' => true, 'msg' => 'Successfully added the captured user image!']);
                } else {
                    return response()->json(['status' => false, 'msg' => 'Error occurred while creating the image!']);
                }

            }


        }
    }
}


public function getAllImages(Request $request)
{
    $validator = Validator::make($request->all(), [
        'sbil_key' => 'required',

    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'msg' => 'Please supply all the required values. Please try later!']);
    }

    $validatedData = $validator->validated();
    $linkKey = trim($validatedData['sbil_key']);
    $linkDetails = $this->KfdService->checkLinkKeyExist($linkKey);

    if (!$linkDetails) {
        return response()->json([
            'status' => false,
            'msg' => 'Given Link is not valid!'
        ], 404);
    }

    // print_r($linkDetails);die;
    $consent = !empty($linkDetails['consent_image_url']) ? json_decode($linkDetails['consent_image_url'], true) : [];
    $reg = !empty($linkDetails['reg_photo_url']) ? json_decode($linkDetails['reg_photo_url'], true) : [];

    $allImages = array_merge($consent, $reg);

    $finalArr = array_map(function ($row) {
        unset($row['latitude'], $row['longitude'], $row['location'], $row['language']);
        return $row;
    }, $allImages);

    return response()->json([
        'status' => true,
        'image_load' => $finalArr
    ], 200);
}



}
