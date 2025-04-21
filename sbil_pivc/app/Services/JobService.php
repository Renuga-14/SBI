<?php

namespace App\Services;

use App\Models\Link;
use App\Repositories\CommonRepository;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;

class JobService
{
    protected $commonRepository;
    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }
    public function getCompleteDateNull()
    {
        return Link::where(function ($query) {
                $query->whereNull('completed_on')
                      ->orWhere('completed_on', '');
            })
            ->where('complete_status', 1)
            // ->where('status', 1)
            ->get();
    }
    public function setCompleteDateNull($link_id, $updateArr)
    {
        return $this->commonRepository->updateRecord(config('constants.LINKS_TABLE'), ['id' => $link_id], $updateArr);

    }

public function getCompleteRR($order_by = 'DESC')
{
    $query = Link::where(function ($q) {
                    $q->whereNull('transcript_pdf_url')
                    ->orWhere('transcript_pdf_url', '');
                })
                ->where('complete_status', 1)
                ->where('status', 0)
                ->whereIn('product_id', [372, 373, 374])
                ->orderBy('created_on', $order_by);


    return $query->get();
}
public function getCompletePIVC($order_by = 'DESC')
{
    $query = Link::where(function ($q) {
        $q->whereNull('transcript_pdf_url')
          ->orWhere('transcript_pdf_url', '');
    })
    ->where('complete_status', 1)
    ->where('status', 1)
    ->whereNotIn('product_id', [372, 373, 374])
    ->orderBy('created_on', $order_by);

    return $query->get();
}

public function formatPDFCollectedData($linkDetails,  $regPhotoUrl = [],  $consentImageUrl = [],  $response = [])
{ 
    $result = [];

    // Get product ID and page order
    $productId = CommonHelper::check_had_value($linkDetails['product_id'] ?? null); 
    $productPageOrder = $this->productPageOrder($productId);
  
    // Format registration photos by screen key
    $regPhotoArr = collect($regPhotoUrl)->keyBy('screen')->toArray();
  
    // Format consent images by screen key
    $consentImageArr = collect($consentImageUrl)->keyBy('screen')->toArray();

    // Format responses by page key
    $responseArr = [];
    if (!empty($response)) {
        $formattedResponses = collect($response)->map(function ($value, $key) {
            $value['slug'] = $key;
            return $value;
        });

        $responseArr = $formattedResponses->keyBy('page')->toArray();
    }

    // Merge data based on product page order
    foreach ($productPageOrder as $pageKey) {
        $result[$pageKey]['image'] = $regPhotoArr[$pageKey] ?? $consentImageArr[$pageKey] ?? null;
        $result[$pageKey]['response'] = $responseArr[$pageKey] ?? null;
    }

    return $result;
}

    public function productPageOrder($product_id)
    {
        // Grouping Product IDs for each type
        $simpleFlow = [1, 8, 10];

        $onlineFlow = [
            2,3,4,5,6,7,62,65,66,99,132,135,136,139,140,145,146,149,151,164,
            170,172,176,179,182,186,188,190,193,195,199,208,213,214,154,215,
            216,219,220,223,224,229,232,235,239,243,245,248,250,253,298,299,
            300,302,305,312,317,319,324,328,331,332,340,344,348,355,356,357,
            358,359,360,361,362,363,371,367,377,380,381,382,390,386
        ];

        $mconnectFlow = [
            11,12,13,14,15,16,17,18,19,20,21,22,24,25,26,27,28,29,30,31,32,33,
            34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,
            56,57,58,59,60,63,64,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,
            82,83,84,85,86,87,88,89,90,91,92,93,94,95,96,97,98,100,101,102,103,
            104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,
            121,122,123,124,125,126,127,128,129,130,131,133,134,137,138,141,142,
            143,144,147,148,150,152,153,155,156,157,158,159,160,161,162,163,165,
            166,168,169,171,173,174,175,177,178,180,181,183,184,185,187,189,191,
            192,194,196,197,198,200,201,202,203,204,205,206,207,209,210,211,212,
            217,218,221,222,225,226,227,228,230,231,233,234,236,237,238,240,241,
            242,244,246,247,249,251,252,254,255,256,257,258,259,260,262,263,264,
            265,266,267,268,269,270,271,272,273,274,285,286,287,275,276,277,278,
            279,280,281,282,283,284,288,289,290,291,292,293,295,296,297,301,303,
            304,306,307,308,309,310,311,313,314,315,318,320,322,327,329,330,323,
            325,326,332,333,334,335,336,337,338,339,341,342,343,345,346,347,349,
            350,351,352,353,354,364,365,366,368,369,370,372,373,374,376,378,379,
            383,384,385,387,388,389
        ];
       
        // Matching Page Order
        if (in_array($product_id, $simpleFlow)) {
            return [
                'Start', 'Language Selection', 'Assets Loading', 'Welcome Screen',
                'Video - Agree', 'Video Uploading', 'Camera Error Page', 'Thank You'
            ];
        } elseif (in_array($product_id, $onlineFlow)) {
            return [
                'Start', 'Language Selection', 'Assets Loading', 'Welcome Screen',
                'Medical Questionnaire', 'Medical Questionnaire - Disagree',
                'Video - Agree', 'Video - DisAgree',
                'OTP - Agree', 'OTP - Disagree',
                'Video Uploading', 'Camera Error Page',
                'Thank You - Disagree', 'Thank You'
            ];
        } elseif (in_array($product_id, $mconnectFlow)) {
            return [
                'Start', 'Language Selection', 'Assets Loading', 'Welcome Screen',
                'Personal Details', 'Personal Details - Confirm', 'Personal Details - Disagree',
                'Policy Details', 'Policy Details - Confirm', 'Policy Details - Disagree',
                'Medical Questionnaire', 'Medical Questionnaire - Confirm', 'Medical Questionnaire - Disagree',
                'Benefit Illustration', 'Benefit Illustration - Confirm', 'Benefit Illustration - Disagree',
                'Product Benefits', 'Product Benefits - Confirm', 'Product Benefits - Disagree',
                'Photo Consent', 'Terms Details', 'Camera Error Page',
                'Photo Uploading', 'Thank You - Disagree', 'Thank You',
                'Medical Confirmation Screen One', 'Medical Confirmation Screen Two'
            ];
        } else {
            return [
                'Start', 'Language Selection', 'Assets Loading', 'Welcome Screen',
                'Medical Questionnaire', 'Video - Agree', 'Video - DisAgree',
                'OTP - Agree', 'OTP - Disagree',
                'Video Uploading', 'Camera Error Page',
                'Thank You - Disagree', 'Thank You'
            ];
        }
    }



}
