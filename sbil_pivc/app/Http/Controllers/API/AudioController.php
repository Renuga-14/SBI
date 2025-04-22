<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\Controller; 
use App\Services\LinkService;
use App\Services\JobService;
use Illuminate\Support\Str;


class AudioController extends Controller
{
    protected $linkService;
    protected $jobService;
    public function __construct(JobService $jobService,LinkService $linkService)
    {
        $this->linkService = $linkService;
        $this->jobService = $jobService;
    }
    public function playAudioFromPDF($encryptedProposalNo, $screen)
    {
                // $proposalNo = Crypt::decryptString($encryptedProposalNo);
        $proposalNo = $encryptedProposalNo; //print_r($proposalNo);die;
        $basePath =  "/var/www/html/sbil_piwc/";
        $links = $this->linkService->checkProposalNoExistDetailspdf($proposalNo);
        if (!$links) {
            return response()->json(['error' => 'Proposal not found'], 404);
        }
        $completedYear = date('Y', strtotime($links['completed_on']));
        $completedYearMinusOne = $completedYear - 1;
        $data_list = $this->jobService->formatPDFCollectedData($links,json_decode($links['reg_photo_url'],TRUE),json_decode($links['consent_image_url'],TRUE),json_decode($links['response'],TRUE));  

        $lang = $data_list['Welcome Screen']['image']['language'] ?? 'eng';
        

        $parameters = json_decode($links['params']);
        $flow_key = $parameters->flow_key ?? null;
        $flow_data = $parameters->flow_data ?? null;

        // Handle guaranteed frequency for Smart Platina Plus
        $smart_platina_keys = [
            'sbilm_smart_platina_plus', 'sbilsa_smart_platina_plus',
            'sbilo_smart_platina_plus', 'sbily_smart_platina_plus',
            'sbilpl_smart_platina_plus'
        ];

        if (in_array($flow_key, $smart_platina_keys)) {
            $guaranteed_frequency = strtolower(str_replace('_', '', $flow_data->strGuaranteedPayoutFrequency ?? ''));
        }

        // Normalize frequency
        $payment_frequent = strtolower(str_replace(['_', ' ', '-'], ['', '_', '_'], $flow_data->FREQUENCY ?? ''));

        // Welcome screen sounds
        if (Str::contains(Str::lower(str_replace(' ', '', $screen)), 'welcomescreen')) {
            $sound_array = [];

            if (in_array($links['source'], [3, 8])) {
                $sound_array[] = 'welcome_online';
            } else {
                $retire_or_platina_keys = array_merge([
                    'sbilm_retire_smart_plus', 'sbilsa_retire_smart_plus',
                    'sbilo_retire_smart_plus', 'sbily_retire_smart_plus',
                    'sbilpl_retire_smart_plus'
                ], $smart_platina_keys);

                if (in_array($flow_key, $retire_or_platina_keys)) {
                    $sound_array[] = 'welcome';
                } elseif (in_array($flow_key, ['sbilm_rinn_raksha', 'sbilpn_rinn_raksha', 'sbilmp_rinn_raksha'])) {
                    $basePath = "/var/www/html/sbil_piwc_rinn_raksha/";
                    $loan_category = $flow_data->LOAN_CATEGORY ?? '';

                    if ($loan_category == "Personal Loan") {
                        $sound_array = ['welcome_one', 'personal_loan', 'welcome_two'];
                    } elseif ($loan_category == "Home Loan") {
                        $sound_array = ['welcome_one', 'home_loan', 'welcome_two'];
                    } else {
                        $sound_array[] = 'welcome';
                    }
                } else {
                    $sound_array[] = 'welcome';
                }
            }
        }
        $screen_key = strtolower(str_replace(' ', '', $screen));
        $rinn_raksha_keys = ['sbilm_rinn_raksha', 'sbilpn_rinn_raksha', 'sbilmp_rinn_raksha'];
        
        // Check for Rinn Raksha flow
        if (in_array($flow_key, $rinn_raksha_keys)) {
            $basePath = "D:/xampp/htdocs/sbil_piwc_rinn_raksha/";
            // $basePath = "/var/www/html/sbil_piwc_rinn_raksha/";
        
            // Medical Questionnaire Screen
            if (Str::contains($screen_key, 'medicalquestionnaire-disagree')) {
                $sound_array[] = '4_2';
            } elseif (Str::contains($screen_key, 'medicalquestionnaire')) {
                $sound_array[] = '5_1';
            }
        
            // Personal Details Screen
            if (Str::contains($screen_key, 'personaldetails-disagree')) {
                $sound_array[] = '4_2';
            } elseif (Str::contains($screen_key, 'personaldetails')) {
                $sound_array[] = '3_1';
            }
        
        } else {
            // Non-Rinn Raksha flow for Personal Details
            if (Str::contains($screen_key, 'personaldetails-disagree')) {
                $sound_array[] = '3_2';
            } elseif (Str::contains($screen_key, 'personaldetails')) {
                $sound_array[] = '3_1';
            }
        }
       
       
        $retire_smart_keys = [
            "sbilm_retire_smart", "sbilsa_retire_smart", "sbilo_retire_smart",
            "sbilm_retire_smart_plus", "sbilsa_retire_smart_plus", "sbilo_retire_smart_plus",
            "sbily_retire_smart_plus", "sbilpl_retire_smart", "sbilpl_retire_smart_plus"
        ];
        
        if (!in_array($parameters->flow_key, $retire_smart_keys)) {
            define('PRODUCT_AUDIO_LANG_PATH_NO', $basePath . 'assets/product_assets/' . $parameters->flow_key . '/audio/' . $lang . '/scenes/');
        } else {
            define('PRODUCT_AUDIO_LANG_PATH_NO', $basePath . 'assets/audio/product/' . $lang . '/scenes/');
        }
        
        define('COMMON_AUDIO_LANG_PATH_NO', $basePath . 'assets/audio/product/' . $lang . '/common/');
        
      
        $productAudio = [
            'welcome', 'welcome_one', 'welcome_two', 'home_loan', 'personal_loan',
            'welcome_online', 'welcome_plus', 'vid_online', 'policy_term_single',
            'premium_of_single', 'dis_aud', '3_1', '6_3_1', '6_2_regular', '6_2_single',
            '3_2', '4_2', '5_1', '6_1', '6_2', '6_3', '6_4', '6_5', '6_6', '6_7',
            'PB', '11_1', 'DB',
        ];
        
        $commonAudio = [
            'premium_of', 'req_tax', 'applicable_tax', 'for', 'premium_till',
            'policy_term', 'payable_till', 'sum_assured', 'policy_payment_disclaimer',
            'years', 'marathi_policy_extra_audio', 'zero', 'one', 'two', 'three', 'four',
            'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve',
            'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen',
            'nineteen', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy',
            'eighty', 'ninety', 'hundred', 'thousand', 'lakh', 'crore', 'hundreds',
            'thousands', 'lakhs', 'million', 'crores', 'rupees', 'and', 'paisa'
        ];
        // Dynamic values
    /*     $dynamicCommon = [
            $product_name, $payment_type, $payment_frequent,
            $payment_term, $payment_term_in_years, $benefit_term
        ];
         */
        $dynamicProduct = [];

        if (!empty($guaranteed_frequency)) {
            $dynamicProduct[] = $guaranteed_frequency;
        }
        $audio_array = [];
        
        // Add product audio files
        foreach ($sound_array as $sound) {
            if (in_array($sound, $productAudio)) {
                $audio_array[$sound] = PRODUCT_AUDIO_LANG_PATH_NO . $sound . '.mp3';
            } elseif (in_array($sound, $commonAudio)) {
                $audio_array[$sound] = COMMON_AUDIO_LANG_PATH_NO . $sound . '.mp3';
            } else {
                $audio_array[$sound] = 'Not found in audio arrays';
            }
        }//print_r( $audio_array);
        // die; 
      /*   foreach ($productAudio as $key) {
            // $audio_array[$key]= 
            $audio_array[$key] = PRODUCT_AUDIO_LANG_PATH_NO . $audio_array[$sound_array] . '.mp3';
        } */
       /*  print_r($audio_array[$key]);
        die; */
        // Add common audio files
        if (in_array($sound_array, $commonAudio)) {
            $audio_array[$sound_array] = PRODUCT_AUDIO_LANG_PATH_NO . $sound_array . '.mp3';
            echo $audio_array[$sound_array]; // Output the file path
        }
     /*    foreach ($commonAudio as $key) {
            $audio_array[$key] = COMMON_AUDIO_LANG_PATH_NO . $key . '.mp3';
        } */
        
        // Add dynamic common audio files
      /*   foreach ($dynamicCommon as $key) {
            $audio_array[$key] = COMMON_AUDIO_LANG_PATH_NO . $key . '.mp3';
        } */
        
        // Add dynamic product audio files
        if (in_array($sound_array, $dynamicProduct)) {
            $audio_array[$sound_array] = PRODUCT_AUDIO_LANG_PATH_NO . $sound_array . '.mp3';
            echo $audio_array[$sound_array]; // Output the file path
        }
   /*      foreach ($dynamicProduct as $key) {
            $audio_array[$key] = PRODUCT_AUDIO_LANG_PATH_NO . $key . '.mp3';
        } */

    /*    print_r( $audio_array);
        die; */
      $prepare_audioArray   = array(); 
      $iset =0;
      
      //header('Content-type: audio/mpeg');
      //header("Content-Transfer-Encoding: binary");  
      //header("Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3"); 
 
      $audio_html = '<!DOCTYPE html>
      <html>
      <body style="width: 99%;text-align: center;"> 
      <style>
      @media screen and (min-device-width: 800px) and (max-device-width: 1600px) { 
        .topimg { width: 30% !important; } 
      }
      .img-playbtn {
        position: absolute; 
        top: 50%;
        left: 46%;
      }
      </style>';
      
      // Convert screen name to lowercase and remove spaces
      $screen_clean = strtolower(str_replace(" ", "", $screen));
      
      // Map screen keys to corresponding image keys
      $screen_map = [
        'welcomescreen' => 'Welcome Screen',
        'personaldetails-disagree' => 'Personal Details - Disagree',
        'personaldetails' => 'Personal Details',
        'policydetails-disagree' => 'Policy Details - Disagree',
        'policydetails' => 'Policy Details',
        'medicalquestionnaire-disagree' => 'Medical Questionnaire - Disagree',
        'medicalquestionnaire' => 'Medical Questionnaire',
        'medicalconfirmationscreenone' => 'Medical Confirmation Screen One',
        'medicalconfirmationccreentwo' => 'Medical Confirmation Screen Two',
        'benefitillustration-disagree' => 'Benefit Illustration - Disagree',
        'benefitillustration' => 'Benefit Illustration',
        'finalconfirmationscreen' => 'Final Confirmation Screen',
        'deathbenefits-disagree' => 'Product Benefits - Disagree',
        'productbenefits-disagree' => 'Product Benefits - Disagree',
        'deathbenefits' => 'Product Benefits',
        'productbenefits' => 'Product Benefits',
        'importantpoints' => 'Terms Details',
        'termsdetails' => 'Terms Details',
      ];
   
      // Loop through the map and match the screen
      foreach ($screen_map as $key => $image_key) {   
        if (strpos($screen_clean, strtolower(str_replace(" ", "", $key))) !== false) {
          if (isset($data_list[$image_key]['image']['media_screen_url'])) {
            $image_url = $data_list[$image_key]['image']['media_screen_url'];
            $audio_html .= '<img class="topimg" src="' . $image_url . '" style="width: 100%; margin: 0 auto;" />';
          }
          break;
        }
      }//print_r($data_list[$image_key]);die;
      
      // Add play button
      $audio_html .= '<div class="img-playbtn" id="img-playbtn">
        <img src="https://pivc.sbilife.co.in/sbil_piwc/assets/images/common/playbtn.png" style="width: 78px;" onclick="play()" /> 
      </div>
      <script>
      function play() {
        document.getElementById("img-playbtn").style.display = "none";
      ';
      
      // Handle audio play sequence
      $previous_index = null;
      $key_index = 0;
    //   print_r($audio_array);die;
      foreach ($sound_array as $key => $audioStr) { 
        if (isset($audio_array[$audioStr]) && file_exists($audio_array[$audioStr])) {
          $audio_update = str_replace('D:/xampp/htdocs', 'http://localhost', $audio_array[$audioStr]);
        //   $audio_update = str_replace('public_html', '', $audio_update);print_r($audio_update);
     /*      $audio_update = str_replace('/var/www/html', 'https://pivc.sbilife.co.in', $audio_array[$audioStr]);
          $audio_update = str_replace('public_html', '', $audio_update); */ 
          $index_var = 'index' . $key_index;
      
          $audio_html .= "var $index_var = new Audio(\"$audio_update\");\n";
        
          if ($previous_index === null) {
            $audio_html .= "$index_var.play().catch(e => {
              alert('Playback failed. Click again or check permissions.');
              document.getElementById('img-playbtn').style.display = 'block';
            });\n";
          } else {
            $audio_html .= "$previous_index.onended = function() { $index_var.play(); };\n";
          }
          
          $previous_index = $index_var;
          $key_index++;
        }
      }
      
      $audio_html .= '}
      </script>
      </body>
      </html>';
     
      echo $audio_html;
      die;
      
        // print_r($audio_array);
    }
}
