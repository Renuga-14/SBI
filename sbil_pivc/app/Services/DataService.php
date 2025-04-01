<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Link;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CommonRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DataService
{
    protected $commonRepository;
    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }
    public function consentImageFile($linkId, $scrn = null)
    {
        $fileData = [
            'status' => false,
            'name' => '',
            'p_name' => ''
        ];

        $paramData = $this->getParamData($linkId);
     
        if (!empty($paramData)) {
            $fileName = '';
            $fileName .= (!empty($paramData['proposal_no'])) ? $paramData['proposal_no'] . '_' : '';
            $fileName .= 'PIVCPHOTO_';
            //$fileName .= (!empty($scrn)) ? $scrn . '_' : '';
            $fileName .= Carbon::now()->format('Y_m_d_H_i_s');
            $fileName = CommonHelper::fileNameStd($fileName);

            $fileData['status'] = true;
            $fileData['name'] = $fileName;
            $fileData['p_name'] = (!empty($paramData['flow_key'])) ? $paramData['flow_key'] : '';
        }

        return $fileData;
    }
    public function getParamData($linkId)
    {
        $link = Link::select('id', 'ukey', 'params')->where('id', $linkId)->first();

        if ($link && !empty($link->params)) {
            $paramArr = json_decode($link->params, true);
            return !empty($paramArr) ? $paramArr : null;
        }

        return null;
    }

    public function addConsentJPEGImageFile($fileDetails, $fileData, $metaDetails = null)
    {
        $imgFileData = [
            'status' => false,
            'name'   => '',
            'path'   => '',
            'url'    => '',
            'key'    => ''
        ];
    

        // Define storage paths
        // $dataDir = public_path(config('filesystems.paths.df_path')); 
        $dfPath = config('filesystems.paths.df_path');
        $path = public_path(trim($dfPath, '/'));
        $dataDir = Str::replace('\\', '/', $path);


        $imgDirRel = '/'.$fileDetails['file_loc'] . $fileDetails['product_name'] . '/';
        $imgDir = $dataDir . $imgDirRel;
       
        $dirStatus = CommonHelper::makeDirs($imgDir);
      
        // File details
        $ext = '.jpeg';
        $imgName = $fileDetails['file_name'] . $ext;
        $imgPath = $imgDir . $imgName;
        $imgUrl = asset($imgDirRel . $imgName);
        $imgKey = $imgDirRel . $imgName;
        // dd(config('filesystems.paths.df_path'));

     
       
        // Save the file
        if (file_put_contents($imgPath, ($fileData))) {
            $imgFileData = [
                'status' => true,
                'name'   => $imgName,
                'path'   => $imgPath,
                'url'    => $imgUrl,
                'key'    => $imgKey
            ];

            // If AWS environment, upload to S3
            if (config('app.env') == 'aws') {
                $awsFileUpload = $this->dataImageS3Upload($imgPath, $imgKey);

                if ($awsFileUpload['status']) {
                    $imgFileData['url'] = $awsFileUpload['url'];
                } else {
                    $imgFileData['status'] = false; // AWS upload failed
                }
            } else {
                $awsFileUpload = $this->dataImageLocalUpload($imgPath, $imgKey);
                if ($awsFileUpload['status']) {
                    $imgFileData['url'] = $awsFileUpload['url'];
                } else {
                    $imgFileData['status'] = false; // AWS upload failed
                }
            }
        }

        return $imgFileData;
    }


    public function dataImageS3Upload($path, $key)
    {
        $res = [
            'status' => false,
            'url' => ''
        ];

        
        try {
            // Upload file to S3
            $upload = Storage::disk('s3')->put($key, file_get_contents($path), 'public');

            if ($upload && Storage::disk('s3')->exists($key)) {
                $res['status'] = true;
                $res['url'] = Storage::disk('s3')->url($key);
            }
        } catch (\Exception $e) {
            \Log::error("S3 Upload Error: " . $e->getMessage());
        }

        return $res;
    }

// local use only
    public function dataImageLocalUpload($path, $key)
    {
        
        $res = [
            'status' => false,
            'url' => ''
        ];
    
        try {
            // Define local storage path (public folder)
            $destinationPath = storage_path('app/public/' . $key);
          
            // Ensure the directory exists
            if (!file_exists(dirname($destinationPath))) {
                mkdir(dirname($destinationPath), 0777, true);
            }
    
            // Copy the file to the local storage
            if (copy($path, $destinationPath)) {
                $res['status'] = true;
                $res['url'] = asset('storage/' . $key); // Accessible via browser
            }
        } catch (\Exception $e) {
            \Log::error("Local File Upload Error: " . $e->getMessage());
        }
    
        return $res;
    }
    
        public function updateConsentPhotoUrl($id, $mediaUrl, $mediaAppend = false, $infoParams)
    {
        $infoArr = [];
        $table = 'links'; // Replace with the actual table name

        if (!$mediaAppend) {
            $infoParams['media_url'] = $mediaUrl;
            array_push($infoArr, $infoParams);
            $mediaListStr = json_encode($infoArr);

            return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $id], ['consent_image_url' => $mediaListStr]);

        } else {
            $arr = ['Medical Questionnaire', 'Welcome Screen', 'Benefit Illustration'];

            $res = DB::table(config('constants.LINKS_TABLE'))
                ->where('id', $id)
                ->select('id', 'consent_image_url', 'reg_photo_url')
                ->first();

            $mediaListStr = $res->consent_image_url ?? '';
            $mediaListArr = [];

            if (!empty($mediaListStr)) {
                $mediaListArr = $this->removeImageJson($mediaListStr, $infoParams['screen']);
            }

            $infoParams['media_url'] = $mediaUrl;
            array_push($mediaListArr, $infoParams);
            $mediaListStr = json_encode($mediaListArr);

            $mediaListStr1 = $res->reg_photo_url ?? '';
            $mediaListArr1 = json_decode($mediaListStr1, true) ?? [];
            $mediaListArr1 = array_values($mediaListArr1);
            $screen = [];


            foreach ($mediaListArr1 as $key) {
                $screen[] = preg_replace('/ - Disagree/', '', trim($key['screen']));
            }

            if (in_array($infoParams['screen'], $arr) && in_array($infoParams['screen'], $screen)) {
                $mediaListArr1 = $this->removeImageJson($mediaListStr1, $infoParams['screen']);
                $mediaListStr1 = json_encode($mediaListArr1);                
                  return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $id], ['reg_photo_url' => $mediaListStr1]);
            }
            return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $id], ['consent_image_url' => $mediaListStr]);
        }
    }

    public function removeImageJson($media_list_str, $info_params_screen)
{
    // Decode JSON and validate
    $temp_list = json_decode($media_list_str, true);
    if (!is_array($temp_list)) {
        return []; // Return empty array if decoding fails
    }

    $media_list_arr = [];

    // Build the media list array
    foreach ($temp_list as $rs) {
        if (!isset($rs['screen'])) continue; // Ensure 'screen' key exists

        $screen = $this->chgName($rs['screen']);
        if (isset($media_list_arr[$screen])) {
            $media_list_arr[$screen . '_disagree'] = $rs;
        } else {
            $media_list_arr[$screen] = $rs;
        }
    }


    // Determine removal target
    $rmv_screen = $this->chgName($info_params_screen);
    $rmv_screen_count = substr_count($info_params_screen, '-');

    if ($rmv_screen_count == 1) {
        unset($media_list_arr[$rmv_screen . '_disagree']);
    } else {
        unset($media_list_arr[$rmv_screen]);
        unset($media_list_arr[$rmv_screen . '_disagree']);
    }
   //print_r($media_list_arr);exit;
   /*$rmv_screen = $this->chgName($info_params_screen);
   $rmv_screen_count = count(explode('-',$info_params_screen));
   if($rmv_screen_count==2)// and in_array($media_list_arr[$rmv_screen.'_disagree'],$media_list_arr))
   {
       //unset($media_list_arr[$rmv_screen]);
       unset($media_list_arr[$rmv_screen.'_disagree']);
   }
   else
   {
       unset($media_list_arr[$rmv_screen]);
       unset($media_list_arr[$rmv_screen.'_disagree']);
   }
   $temp_list_new = array_values($media_list_arr); 
   //print_r($temp_list_new);exit;
   if(!empty($temp_list_new))
   {
       $media_list_arr = $temp_list_new;
   }
   else
   {
       $media_list_arr = array();
   }
   return $media_list_arr;*/
    // Re-index the array
    return array_values($media_list_arr);
}

public static function chgName($str)
{
    $name = preg_replace('/ - Disagree/', '', trim($str));
    $name = str_replace(' ', '_', trim($name));
    return strtolower($name);
}

    public function captureImageScreenShotFile($linkId, $scrn = null)
    {
        $fileData = [
            'status' => false,
            'name' => '',
            'p_name' => ''
        ];

        $paramData = $this->getParamData($linkId);

        if (!empty($paramData)) {
            $fileName = '';
            $fileName .= !empty($paramData['proposal_no']) ? $paramData['proposal_no'] . '_' : '';
            $fileName .= 'PIVCPHOTO_SCREEN_';
            $fileName .= Carbon::now()->format('Y_m_d_H_i_s');
            $fileName = CommonHelper::fileNameStd($fileName);

            $fileData['status'] = true;
            $fileData['name'] = $fileName;
            $fileData['p_name'] = !empty($paramData['flow_key']) ? $paramData['flow_key'] : '';
        }

        return $fileData;
    }
    public function addCapturedJPEGImageFile(array $fileDetails, $fileData, array $metaDetails = null)
    {
        $imgFileData = [
            'status' => false,
            'name' => '',
            'path' => '',
            'url' => '',
            'key' => ''
        ];

        $dfPath = config('filesystems.paths.df_path');
        $path = public_path(trim($dfPath, '/'));
        $dataDir = Str::replace('\\', '/', $path);


        $imgDirRel = '/'.$fileDetails['file_loc'] . $fileDetails['product_name'] . '/';
        $imgDir = $dataDir . $imgDirRel;
       
        $dirStatus = CommonHelper::makeDirs($imgDir);

       // File details
       $ext = '.jpeg';
       $imgName = $fileDetails['file_name'] . $ext;
       $imgPath = $imgDir . $imgName;
       $imgUrl = asset($imgDirRel . $imgName);
       $imgKey = $imgDirRel . $imgName;

        // Save the image
        $imgCreate = file_put_contents($imgPath, ($fileData));

        if ($imgCreate) {
            $imgFileData = [
                'status' => true,
                'name'   => $imgName,
                'path'   => $imgPath,
                'url'    => $imgUrl,
                'key'    => $imgKey
            ];

            // AWS S3 Upload
            if (config('app.env')== 'aws') {
                $awsFileUpload = $this->dataImageS3Upload($imgPath, $imgKey);

                if ($awsFileUpload['status']) {
                    $imgFileData['url'] = $awsFileUpload['url'];
                } else {
                    $imgFileData['status'] = false; // AWS upload failed
                }
            } else {
                $awsFileUpload = $this->dataImageLocalUpload($imgPath, $imgKey);
                if ($awsFileUpload['status']) {
                    $imgFileData['url'] = $awsFileUpload['url'];
                } else {
                    $imgFileData['status'] = false; // AWS upload failed
                }
            }
        }

        return $imgFileData;
    }

  

public function updateScreenShotPhotoUrl($id, $media_url, $media_append = false, $info_params)
{
    if (!$media_append) {
        $info_params['media_screen_url'] = $media_url;
        $media_list_arr = [$info_params];
        $media_list_str = json_encode($media_list_arr);

        return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $id], ['reg_photo_url' => $media_list_str]);
    } else {
        $arr = ['Medical Questionnaire', 'Welcome Screen', 'Benefit Illustration'];

        $media_list_str = DB::table(config('constants.LINKS_TABLE'))
            ->where('id', $id)
            ->select('id', 'reg_photo_url', 'consent_image_url')
            ->first();

        if ($media_list_str) {
            if (in_array($info_params['screen'], $arr)) {
                $media_list_str1 = $media_list_str->consent_image_url;
            } else {
                $media_list_str1 = $media_list_str->reg_photo_url;
            }
          
            $data_fine = json_decode($media_list_str1, true);
            $media_list_final = [];

            if (is_array($data_fine)) {
                foreach ($data_fine as $vg) {
                    if ($vg['screen'] == $info_params['screen']) {
                        $vg['media_screen_url'] = $media_url;
                    }
                    $media_list_final[] = $vg;
                }
            }

            if (!empty($media_list_final)) {
                $updated_media_list_str = json_encode($media_list_final);
                $update_column = in_array($info_params['screen'], $arr) ? 'consent_image_url' : 'reg_photo_url';
              $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $id], [$update_column => $updated_media_list_str]);
            }
        }

        return;
    }
}
public function captureImageFile($linkId, $scrn = null)
{
    $fileData = [
        'status' => false,
        'name' => '',
        'p_name' => ''
    ];

    $paramData = $this->getParamData($linkId);

    if (!empty($paramData)) {
        $fileName = (!empty($paramData['proposal_no']) ? $paramData['proposal_no'] . '_' : '') . 
                    'PIVCPHOTO_' . 
                    now()->format('Y_m_d_H_i_s'); // Using Laravel's `now()`

         $fileName = CommonHelper::fileNameStd($fileName);

        $fileData = [
            'status' => true,
            'name' => $fileName,
            'p_name' => $paramData['flow_key'] ?? ''
        ];
    }
    return $fileData;
}



}
