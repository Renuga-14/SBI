<?php

namespace App\Http\Controllers\Cron;

use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Services\JobService;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Services\LinkService;
use App\Constants\Products;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use App\Http\Controllers\Cron\PDFTextController;

require_once base_path('vendor/autoload.php');

class JobController extends Controller
{
    protected $jobService;
    protected $linkService;

    public function __construct(JobService $jobService,LinkService $linkService)
    {
        $this->jobService = $jobService;
        $this->linkService = $linkService;
        $this->data=array();
        $this->data['title']="Job API";
    }
    // generate pdf 
    public function generateTranscriptPDF($case="RR")
    {
    //   $this->PendingCompletedPIVCflagPush(); // PendingFlagPush

      $this->linkService->addLog(
        'genTransPDF',
        json_encode(['type' => "Cron Job : Generate Transcript PDF- "]),
        NULL,
        0
    );
    switch (strtoupper($case)) {
        case 'RR':
            $pivcCompleteList = $this->jobService->getCompleteRR();   
            if (!$pivcCompleteList->isEmpty()) {
                $this->pdfGenerate($pivcCompleteList);
            } else {
                return response()->json(['error' => 'RinnRaksha Records are empty.'], 400);
            }
            break;
          
           
        case 'PIVC':
            $pivcCompleteList = $this->jobService->getCompletePIVC(); 
            if (!$pivcCompleteList->isEmpty()) {
                $this->pdfGenerate($pivcCompleteList);
            } else {
                return response()->json(['error' => 'PIVC Records are empty.'], 400);
            }
            break;
        default:
            return response()->json(['error' => 'Invalid case provided. Please use RR or PIVC only.'], 400);
    }
// print_r($case);
    }

    // PendingFlagPush 
    public function PendingCompletedPIVCflagPush () {
        $completeNullLinks = $this->jobService->getCompleteDateNull(); 
     
        if ($completeNullLinks->isNotEmpty()) {
            foreach ($completeNullLinks as $link) {
                $linkId = $link->id;
                $updated = $link->updated_on;
                
                $updateArr = [
                    'completed_on' => $updated,
                ];
            //    $this->jobService->setCompleteDateNull($linkId, $updateArr); 
               $resArr = json_decode($link['response'],true); 
               $paramsdata = json_decode($link['params'], true); 
               if (isset($paramsdata['flow_key']) && strpos(strtolower((string) $paramsdata['flow_key']), '_rinn_raksha') !== false ) { 
                $statusremark =  CommonHelper::pivcRinnRakshaRemarks(1, $link['disagree_status'], $resArr, now()->format('m/d/Y H:i'));
                $statusData = array(
                    'FORM_NUM' => $paramsdata['flow_data']['FORMNUMBER'],
                    'PL_POL_NUM' => $paramsdata['flow_data']['RD_MASTER_POLICY_NO'],
                    'LOAN_ACCT_NUM' => $paramsdata['flow_data']['LOANACCOUNTNUMBER'],
                    'LOAN_PLUS_ACCT_NUM' => $paramsdata['flow_data']['LOANACCOUNTNUMBER'],
                    'PIWC_CALL_FLAG' => $statusremark['piwc_call_flag'],
                    'PIWC_MED_FLAG' =>$statusremark['piwc_med_flag'],
                    'LATEST_CALL_DATE' =>  date('m/d/Y'),
                    'CALL_TIME' => date('H:i'),
                    'CUST_NAME' =>  $paramsdata['flow_data']['POLICY_HOLDER_NAME'],
                    'RESIDENCE_CONTACT' =>  "0",
                    'OFFICE_CONTACT' =>  "0",
                    'MOBILE_NO' =>  $paramsdata['flow_data']['MOBILE_NUMBER'],
                    'PRECALLING_STATUS' =>  $statusremark['precalling'],
                    'MAIN_REASON' =>  $statusremark['mainreason'],
                    'SUB_REASON' =>  $statusremark['sub_reason'],
                    'CALLING_REMARKS' =>   $statusremark['remarks'],
                    "SOURCE" => "Anoor",
                );
              
                $this->linkService->updateGroupPIWCDetailsInsta($statusData);
            } else {
                $allowedStatusType = array(2 => 'MCNCT',3 => 'ONLINE',5 => 'SMTADV',6 => 'PHYSICAL');
                if(CommonHelper::pivcFullRemarks($link['complete_status'],$link['disagree_status'],$resArr)=='Mismatch')
                {
                    $vd_verif = 'D';
                }
                else
                {
                    $vd_verif = 'N';
                }
                $statusData = array(
                    'VD_SUBMIT_DATE'=>date('m/d/Y',strtotime($updated)),
                    'PIVC_CALL_FLAG'=>CommonHelper::pivcFullRemarkStatus(1,$link['disagree_status'],$resArr),
                    'PIVC_TYPE'=>$allowedStatusType[$link['source']],
                    'REMARKS'=>CommonHelper::pivcFullRemarks(1,$link['disagree_status'],$resArr),
                    'FacialScorePercentage'=>0,
                    'VD_Verif'=>$vd_verif
                );
                $this->linkService->updatePIVCStatusAPI($link['proposal_no'], $statusData);
            }
            }
        }

    }

    public function pdfGenerate($pivcCompleteList){
        if(!empty($pivcCompleteList))
        { 
            foreach ($pivcCompleteList as $pNCKey=>$pNCValue)
            {
               
                $link_params_arr = CommonHelper::check_had_value($pNCValue['params'])? json_decode($pNCValue['params'],TRUE):NULL;
                $this->data['link_details'] = $pNCValue; 
                $this->data['link_params'] = $link_params_arr;
                $this->data['device'] = json_decode($pNCValue['device'],true); 
                $reg_photo_url = CommonHelper::check_had_value($pNCValue['reg_photo_url'])  ? json_decode($pNCValue['reg_photo_url'],TRUE):NULL;
                $consent_image_url = CommonHelper::check_had_value($pNCValue['consent_image_url'])? json_decode($pNCValue['consent_image_url'],TRUE):NULL;
                $response = CommonHelper::check_had_value($pNCValue['response'])? json_decode($pNCValue['response'],TRUE):NULL;
                ksort($response);//print_r($response);die;
                $data_list = $this->jobService->formatPDFCollectedData($pNCValue,$reg_photo_url,$consent_image_url,$response);   
               
                $this->data['data_list'] = $data_list; 
                $completed_year = (date('Y', strtotime($pNCValue['completed_on'])));
             
                $flowKey = $this->data['link_params']['flow_key'] ?? '';

                foreach ($data_list as $kee => $dataListValue) {
                    $language = $dataListValue['image']['language'] ?? null;
                    $slugName = $this->getSlugName($flowKey);//print_r($slugName);die;
                    $sbi_url = "https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/";   
                    $screen = isset($dataListValue['image']['screen']) ? strtolower(str_replace(" ", "", $dataListValue['image']['screen'])) : '';

                    $source = $pNCValue['source']; 
                    $loanCategory = $link_params_arr['flow_data']['LOAN_CATEGORY'];
                    $productName = ucwords(str_replace('_',' ', $slugName));
                    $rin = Products::$rin;  
                 
                    if (in_array($flowKey,$rin)) { 
                        if (!empty($screen)) {
                            $this->data['audio_text'][$screen] = PDFTextController::handlePDFTextAllLang('rinnraksha',$slugName, $flowKey, $screen, $source, $loanCategory, $productName, $language,$dataListValue);
                        }
                    } else {
                        $this->data['audio_text'][$screen] = PDFTextController::handlePDFTextAllLang('pivc',$slugName, $flowKey, $screen, $source, $loanCategory, $productName, $language,$dataListValue);
                    } 
                   
                 
                }  //die;
                
                $curDateTime = date('Y-m-d H:i:s');
                // $this->data['face_score'] = $face_score;
                // $this->data['face_response'] = $face_response;
                $this->data['response'] = $response;
                $this->data['curDateTime'] = $curDateTime;
                $this->data['facial'] = 0;//(!empty($face_reg))?$face_reg[0]:0;
                // $this->data['plan'] = $this->job_model->AnnuityPlan(trim($link_params_arr['flow_data']['PLAN'])); 
                $this->data['facial'] = 0;//(!empty($face_reg))?$face_reg[0]:0;  
                $arrV = array("CUSTOMER_NAME" => "in_name",
                    "MA_DOB_PH" => "in_dob",
                    "MA_GENDER" => "in_gender",
                    "MAILINGADDRESS1" => "in_address",
                    "MAILINGADDRESS2" => "in_address1",
                    "MAILINGADDRESS3" => "in_address2",
                    "MAILINGCITY" => "in_address3",
                    "MOBILE_NUMBER" => "in_mobile_no",
                    "EMAIL" => "in_email",
                    "NOMINEE_NAME" => "in_nominee_name",
                    "NOMINEE_RELATION" => "in_nominee_relation",
                    "PROPOSER_OCCUPATION" => "in_occupation");
                $flipArr = array_flip($arrV);
                $this->data['PersonalLabel'] = $flipArr;
                if (strpos($pNCValue['link'], 'adc') !== false) {
                    $transcript_pdf_html = view('template.pdf.transcript.transcript', $this->data)->render();
                } elseif (strpos($pNCValue['link'], 'sbil_piwc_rinn_raksha') !== false) {
                    $transcript_pdf_html = view('template.pdf.transcriptrinnraksha', $this->data)->render();
                } else {
                    $transcript_pdf_html = view('template.pdf.transcriptNewDesign', $this->data)->render();
                }//print_r($transcript_pdf_html);die;
                $flowKey = (!empty($link_params_arr['flow_key']))? $link_params_arr['flow_key']:'';
                $dataDir = public_path();
                $fileDirRel = 'df_adc_transcript_file_path/' . $flowKey . '/';
                $fileDir = $dataDir . '/' . $fileDirRel;
             /*    $data_dir = FCPATH.DF_PATH;
                $file_dir_rel = DF_ADC_TRANSCRIPT_FILE_PATH.$flow_key.'/';
                $file_dir = $data_dir.$file_dir_rel;

                $dir_status = $this->common_model->makeDirs($file_dir); */
              
                $dirStatus = CommonHelper::makeDirs($fileDir);
				$completedDateTime = CommonHelper::check_had_value($pNCValue['completed_on'])? date('Y-m-d H:i:s',strtotime($pNCValue['completed_on'])):date('Y-m-d H:i:s',strtotime($curDateTime));
                            
                $fileName = '';
                $fileName .= !empty($pNCValue['proposal_no']) ? $pNCValue['proposal_no'] . '_' : '';
                $fileName .= 'PIVCTRST_';
                $fileName .= !empty($pNCValue['version']) ? 'V' . $pNCValue['version'] . '_' : '';
                $fileName .= date('Y_m_d_H_i_s', strtotime($completedDateTime));
                $fileName .=CommonHelper::fileNameStd($fileName);
                $fileName .= '.pdf';
                            
                $filePath = $fileDir . $fileName;
                $fileUrl = asset($fileDirRel . $fileName);
                $fileKey = $fileDirRel . $fileName;
              

                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P',
                    'margin_left' => 0,
                    'margin_right' => 0,
                    'margin_top' => 0,
                    'margin_bottom' => 10,
                    'defaultfooterline' => 0,
                    'tempDir' => base_path('tmp')  // Laravel-friendly path
                ]);
               
                $mpdf->SetHTMLFooter(
                    '<table width="100%">
                        <tr>
                            <td width="33%">{DATE j-m-Y}</td>
                            <td width="33%" align="center">{PAGENO}/{nbpg}</td>
                            <td width="33%" style="text-align: right;">Insta PIV - Version ' . $pNCValue["version"] . '</td>
                        </tr>
                    </table>'
                );
                
                // Write HTML to PDF
                $mpdf->WriteHTML($transcript_pdf_html);
                
             
                
                // Save PDF to file
                $mpdf->Output($filePath, Destination::FILE);
                
                // Store file info in array
                $file_data = [
                    'key'  => $fileKey,
                    'path' => $filePath,
                ];
           

// Define path based on flow key
$folder = in_array($link_params_arr['flow_key'], ['sbilm_rinn_raksha', 'sbilpn_rinn_raksha', 'sbilmp_rinn_raksha'])
    ? 'IMAGESOURCE/RINNINSTAPIV'
    : 'IMAGESOURCE/Docs';

// Extract file info
$sourcePath = $file_data['path'];
$filename = basename($sourcePath);

// Target path inside Laravel's storage
$destinationPath = 'public/' . $folder . '/' . $filename;
   
// Make sure the file exists
if (file_exists($sourcePath)) {
    // Store (copy) the file to the public storage folder
    Storage::put($destinationPath, file_get_contents($sourcePath));

    // Optionally, get the public URL to access it
    $publicUrl = Storage::url($folder . '/' . $filename);
    // $publicUrl = Storage::url($folder . '/' . $filename);
} else {
    // Handle missing file
    $publicUrl = null;
}

$this->jobService->updatePDFUrl($pNCValue['id'],$sourcePath);          
print_r($sourcePath);die;
            }
        }
    }

/*     function handleRinnRaksha($slugName, $flowKey, $screen, $source, $loanCategory, $productName, $language, $dataListValue) {
        $lowerScreen = strtolower($screen);
        $screenKey = strtolower(str_replace(" ", "", $dataListValue['image']['screen']));
        $screenType = '';
    
        // Identify screen type
        if (strpos($lowerScreen, 'welcomescreen') !== false && in_array($source, [2, 9, 10])) {
            $screenType = 'welcome';
        } elseif (strpos($lowerScreen, 'personaldetails') !== false) {
            $screenType = 'personal';
        } elseif (strpos($lowerScreen, 'medicalquestionnaire') !== false) {
            $screenType = 'medical';
        } elseif (strpos($lowerScreen, 'medicalconfirmationscreenone') !== false) {
            $screenType = 'confirm1';
        } elseif (strpos($lowerScreen, 'medicalconfirmationscreentwo') !== false) {
            $screenType = 'confirm2';
        } elseif (strpos($screenKey, 'medicalquestionnaire-disagree') !== false) {
            $screenType = 'disagree';
        }
    
        switch ($screenType) {
            case 'welcome':
                return "Thank you for choosing SBI life as your preferred life insurance partner. Welcome to the pre-issuance verification process of Your SBI Life - $productName plan chosen by you, to protect your $loanCategory. Your Form number is displayed on the screen. You can quote this Form number for all future communications with us.";
    
            case 'personal':
                return "Please verify the personal details displayed on the screen. Please note these details will form part of your Certificate of Insurance after your proposal is accepted.";
    
            case 'medical':
                return "We would like you to confirm that you have read and answered all the medical questions in the proposal correctly and disclosed all details of medical/treatment history (if any). [Non-disclosure of any adverse medical history may lead to rejection of claim in future]";
    
            case 'confirm1':
                $formattedText = [];
                foreach ($dataListValue['response']['input'] as $key => $value) {
                    if (!empty($value)) {
                        switch ($key) {
                            case "medicalConditionPresent":
                                $formattedText[] = "1. Are you undergoing treatment for any medical condition at present?<br>$value</br>";
                                break;
                            case "medicalConditionPresent_des":
                                if (strtolower($dataListValue['response']['input']['medicalConditionPresent']) == 'yes') {
                                    $formattedText[] = "$value";
                                }
                                break;
                            case "treatmentLast_5years":
                                $formattedText[] = "2. Have you been Hospitalized or operated or underwent treatment for any ailment in last 5 years?<br>$value</br>";
                                break;
                            case "treatmentLast_5years_des":
                                if (strtolower($dataListValue['response']['input']['treatmentLast_5years']) == 'yes') {
                                    $formattedText[] = "$value";
                                }
                                break;
                        }
                    }
                }
                return implode("<br>", $formattedText);
    
            case 'confirm2':
                $formattedText = [];
                $responseInput = $dataListValue['response']['input'];
    
                foreach ($responseInput as $key => $value) {
                    if ($key === "reviewProposalResponse") {
                        $formattedText[] = "1. Do you wish to review your responses given in the proposal form for medical Questionnaire?<br>$value</br>";
                        if (strtolower($value) !== 'yes') break;
                        continue;
                    }
    
                    $questionMap = [
                        "str_rinn_have_you_consulted_any_doctor" => "2. Have you consulted any doctor for surgical operation or have been hospitalized for any disorder other than minor cough, cold or flu during the last 5 years?",
                        "str_rinn_have_you_any_illness_injury" => "3. Have you ever had any illness/injury, major surgical operation or received any treatment for any medical conditions for a continuous period of more than 14 days? (Except for minor cough, cold, flu, appendicitis & typhoid)",
                        "str_rinn_diabetes_raised" => "4. Have you ever suffered from/been treated /hospitalized for a diagnosed to have a) Diabetes, raised blood sugar or high blood pressure?",
                        "str_rinn_chest_pain" => "5. Chest pain, heart attack, heart disease or any other disorder of the circulatory sys Stroke, paralysis, disorder of the brain/nervous system?",
                        "str_rinn_hiv_infections" => "6. HIV infections, AIDS?",
                        "str_rinn_cancer_tumor" => "7. Cancer, tumor, growth or cyst of any kind?",
                        "str_rinn_kidney_disorder" => "8. Any genitourinary or kidney disorder, Hepatitis B/C or any other liver diseases?",
                        "str_rinn_digestive_disorder" => "9. Any digestive disorder (ulcer, colitis etc), any disease of the gall bladder, spleen, any blood disorder or any other gland (e.g. Thyroid etc) or any musculoskeletal disorder?",
                        "str_rinn_asthma" => "10. Asthma, Tuberculosis, Pneumonia, or any other disease of the lung?",
                        "str_rinn_mental_disorder" => "11. Mental, psychiatric or nervous disorder?",
                        "str_rinn_any_other_disease" => "12. Have you suffered from any other disease not mentioned above?",
                        "str_rinn_current_medication" => "13. Are you at present taking any medication, or on any special diet or on any treatment?",
                        "str_rinn_insurance_declined" => "14. Has a proposal for Life insurance, ever been declined, postponed, withdrawn or accepted at extra premium?",
                        "str_rinn_tests_advised_angiography" => "15. Have you had or have been advised to undergo any of the following tests or investigation?",
                        "str_rinn_smoking" => "16. Do you consume more than 10 Cigarettes/bidis per day or chew more than 5 pouches of tobacco per day?",
                        "str_rinn_alcohol_consumption" => "17. Do you consume more than 2 pegs of alcohol per day in any form?",
                        "str_rinn_alcohol_type" => "A. Type : ",
                        "str_rinn_alcohol_quantity" => "B. Quantity : ",
                        "str_rinn_narcotics_usage" => "18. Do you use or have you used any narcotics/any other drugs?",
                        "str_rinn_pregnant" => "19. FEMALE ENSURED ONLY: a) Are you pregnant?",
                        "str_rinn_months_pregnant" => "A. Months in Pregnant:",
                        "str_rinn_gynecological_problems" => "b) Have you suffered from any gynecological problems or illness related to breasts and uterus or ovary?"
                    ];
    
                    $questionText = $questionMap[$key] ?? '';
                    if (!$questionText) continue;
    
                    if (strpos(strtolower($value), '_edit') !== false) {
                        $questionText = "<b style='color:red'>" . $questionText . "</b>";
                        $val = str_replace("_EDIT", "", strtoupper($value));
                        $value = ($val == "TRUE") ? "<b style='color:red'>YES</b>" : (($val == "FALSE") ? "<b style='color:red'>NO</b>" : "<b style='color:red'>" . $val . "</b>");
                    } elseif ($value === true || $value === "true") {
                        $value = "YES";
                    }
    
                    if (in_array($key, ['str_rinn_alcohol_type', 'str_rinn_alcohol_quantity', 'str_rinn_months_pregnant'])) {
                        $formattedText[] = $questionText . $value;
                    } else {
                        $formattedText[] = $questionText . "<br>" . strtoupper($value) . "</br>";
                    }
                }
    
                return implode("<br>", $formattedText);
    
            case 'disagree':
                return "Enter your disagreement in the box provided.";
    
            default:
                return '';
        }
    } */
    

    public function getSlugName($flowKey)
    {
        return str_replace(
            ['_v3', '_v4', 'sbilpl_', 'sbilo_', 'sbilsa_', 'sbilm_'],
            '',
            $flowKey
        );
    }

    

}
