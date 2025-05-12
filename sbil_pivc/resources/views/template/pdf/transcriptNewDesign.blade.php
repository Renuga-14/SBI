<?php

use App\Helpers\CommonHelper;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>SBI LIFE - PIVC Report</title>

    <style>
        .tel {
            font-family: notosans;
        }

        .kan {
            font-family: baloo;
        }

        .hin {
            font-family: freeserif;
        }

        .mar {
            font-family: freeserif;
        }

        .tam {
            font-family: freeserif;
        }

        .mal {
            font-family: freeserif;
        }

        .ori {
            font-family: freeserif;
        }

        .guj {
            font-family: freeserif;
        }

        .pun {
            font-family: freeserif;
        }

        .ben {
            font-family: freeserif;
        }

        .ass {
            font-family: freeserif;
        }

        .maw {
            font-family: freeserif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        .rtl {
            direction: rtl;
        }

        .rtl table {
            text-align: right;
        }

        .rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .tl {
            text-align: left !important;
        }

        .tr {
            text-align: right !important;
        }

        .tc {
            text-align: center !important;
        }

        .w100p {
            width: 100%;
        }

        .w50p {
            width: 50%;
        }

        .w25p {
            width: 25%;
        }

        .w75p {
            width: 75%;
        }

        .wwrap {
            word-wrap: break-word;
            word-break: break-all;
        }

        .bold {
            font-weight: bold;
        }

        .pbg1 {
            background-color: #dde0e6;
        }

        .vam {
            vertical-align: middle;
        }

        .fs24 {
            font-size: 24px;
        }

        .uline {
            text-decoration: underline;
        }
    </style>
</head>


<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title w100p tc">
                                <img src="https://s3.ap-south-1.amazonaws.com/sbi-prod-data/ap/assets/sbi_logo.jpg" style="height:auto; width:200px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="tc">
                                <h3>Insta PIV Transcript</h3> 
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information fs24">
                <td colspan="2">
                    <table>
                    <tr>
                        <td style="font-size: 14px;"> 
                            <span class="bold">Proposal No : </span>{{ CommonHelper::check_had_value($link_params['proposal_no']) ?? '-' }}<br>
                            <span class="bold">Name : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['CUSTOMER_NAME'])  ?? '-'  }}<br>
                            <span class="bold">Product : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['PRODUCT'])  ?? '-' }}<br>
                            <span class="bold">Category : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['PRODUCT_CATEGORY']) ?? '-' }}<br>
                       
                            @if(strpos(CommonHelper::check_had_value($link_params['flow_data']['PRODUCT']), "Annuity Plus") !== false || $link_params['flow_data']['PLAN'] == "")
                                <?php $plan = CommonHelper::AnnuityPlan($link_params['flow_data']['PLAN'], CommonHelper::check_had_value($link_params['flow_data']['PRODUCT_CATEGORY']), $link_details['product_id']); ?>
                            @endif
                            
                            @if($link_params['flow_key'] == 'sbilo_retire_smart')
                                <span class="bold">Plan : </span><br/>
                            @else
                                <span class="bold">Plan : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['PLAN']) }} 
                                @if($link_params['flow_data']['PLAN'] != '') 
                                    ("{{ $link_params['flow_data']['PLAN'] }}")
                                @endif
                                <br/>
                            @endif
                            
                            <span class="bold">Premium Amount : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['PREMIUM_AMOUNT']) }}<br>
                            <span class="bold">Sum Assured : </span>
                            @if(substr($link_params['proposal_no'], 0, 2) != '2R')
                                {{ CommonHelper::check_had_value($link_params['flow_data']['SUM_ASSURED'])?? '-' }}
                            @endif
                            <br>
                            <span class="bold">Frequency : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['FREQUENCY'])?? '-' }}<br>
                            <span class="bold">Term : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['PAYMENT_TERM']) ?? '-'}}<br>
                            <span class="bold">Source : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['APP_SOURCE']) ?? '-'}}<br>
                            <span class="bold">Channel Name : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['CHANNEL_NAME'], '&nbsp;&nbsp;&nbsp;&nbsp;')?? '-'}}<br/>
                            <span class="bold">CIF/Agent ID : </span>{{ CommonHelper::check_had_value($link_params['flow_data']['DISTRIBUTOR_ID']) ?? '-'}}<br/>
                        </td>

                        <td style="font-size: 14px;">
                            @if(!empty($device))
                                <span class="bold">Platform : </span>{{ $device['platform'] }}<br/>
                                <span class="bold">Device Type : </span>{{ $device['device_type'] }}<br/>
                            @endif                    
                            
                            @if(!empty($device))
                                <span class="bold">Device Name : </span>{{ $device['device'] }}<br/>
                                <span class="bold">Browser : </span>{{ $device['browser'] }}<br/>
                            @endif
                            
                            @php
                                $product_array = [192, 197];
                            @endphp
                            
                            @if(empty($link_details['response']) && in_array($link_details['product_id'], $product_array))
                                <span class="bold">Insta PIV Status : </span>Clear Case<br/>
                            @else
                                <span class="bold">Insta PIV Status : </span>{{ CommonHelper::pivcRemarks(1, $response) }}<br/>
                            @endif
                            
                            <span class="bold">Completed On : </span>
                            @if(CommonHelper::check_had_value($link_details['completed_on']))
                                {{ CommonHelper::date_convert($link_details['completed_on'], 'd-M-Y, h:i:s A') }}
                            @endif
                        </td>
                    </tr>
                    </table>
                </td>
            </tr>
@php
$i = 0;


$scrn = [
    'Welcome Screen' => 'welcome',
    'Medical Questionnaire' => 'medical',
    'Benefit Illustration' => 'benefit',
    'Photo Consent' => 'consent',
    'Personal Details' => 'personal',
    'Policy Details' => 'policy',
    'Product Benefits' => 'product',
    'Terms Details' => 'terms'
];

$screenToavoid = [];
// Retire Smart, Annuity Plus, Saral pension
$retireFlowKeys = [
    'sbilm_retire_smart', 'sbilo_retire_smart', 'sbilsa_retire_smart',
    'sbilpl_retire_smart', 'sbilm_saral_pension', 'sbilsa_saral_pension', 
    'sbilpl_saral_pension', 'sbilpl_annuity_plus', 'sbilsa_annuity_plus',
    'sbilo_annuity_plus', 'sbilm_annuity_plus', 'sbilm_retire_smart_plus', 
    'sbilo_retire_smart_plus', 'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus'
];

$screenToavoid = in_array($link_params['flow_key'], $retireFlowKeys) ? 
    ['Medical Questionnaire', 'Medical Questionnaire - Disagree'] : 
    [];
    $product_arr = [
        'sbilm_shudh_nivesh','sbilsa_shudh_nivesh','sbilsa_smart_bachat','sbilm_smart_bachat',
        'sbilm_smart_shield','sbilsa_smart_shield','sbilsa_smart_wealth_builder','sbilm_smart_wealth_builder'
    ];

// Remove Photo Consent from data_list if exists
unset($data_list['Photo Consent']);

if (!empty($data_list)) {
    foreach ($data_list as $key => $value) {
        $score = 0;
        if ($facial != 0) {
            $scn = $scrn[$value['image']['screen']] ?? null;
            if ($scn !== null && isset($facial[$scn])) {
                $score = $facial[$scn];
            }
        }

        $screen = $value['image']['screen'] ?? '';
        if (empty($screen) || in_array($screen, $screenToavoid) || empty($value['response'])) {
            continue;
        }

        $i++;
        $scnKey = $scrn[$screen] ?? '';
        $score = ($facial != 0 && isset($facial[$scnKey])) ? $facial[$scnKey] : 0;
        $mediaUrl = CommonHelper::check_had_value($value['image']['media_url']);
        $langClass = $value['image']['language'] ?? 'en';
        $audioKey = strtolower(str_replace(" ", "", $screen));
        $audioText = $audio_text[$audioKey] ?? '';
        $audioUrl = "http://127.0.0.1:8000/portal/api/data/playaudioFromPDF/" . ($link_params['proposal_no']) . "/" . $audioKey;
@endphp
        <tr>
            <td colspan="2">
            <table class="pbg1">
                <tr class="item">
                    <td colspan="2">
                        <span class="tl bold">Photo {{ $i }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="{{ $mediaUrl }}" width="210px" height="280px" />

                        @if ($audioKey != 'productbenefits')
                            <table>
                                <tr class="item">
                                    <td colspan="2">
                                        <span class="tl">
                                            Audio
                                            <p style="padding:15px;">
                                                <a href="{{ $audioUrl }}" target="_blank">
                                                    <img src="https://cdn.iconscout.com/icon/free/png-256/speaker-2653706-2202518.png" width="20px" height="20px" />
                                                </a>
                                            </p>
                                        </span>
                                        <br>
                                        <span class="t1 {{ $langClass }}">{{ $audioText }}</span>
                                    </td>
                                   
                                </tr>
                            </table>
                        @endif
                    </td>
                    <td style="text-align:left;">
                                        <img src="{{ $value['image']['media_screen_url'] }}" width="250px" height="480px" />
                                    </td>
                </tr>
            </table>

            </td>
        </tr>
@php
    }
}
@endphp
<tr>
    <td colspan="2">
        <table class="pbg1">
            <tr class="item">
                <td colspan="3">
                    <span class="tl bold">Thank You</span>
                </td>
            </tr>

            @php
            $key = $link_params['flow_key'];

            // Group 1: Retire Smart, Annuity, Swadhan products
            $group1 = [
                'sbilm_retire_smart_plus', 'sbilo_retire_smart_plus', 'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus',
                'sbilm_smart_annuity_plus', 'sbilsa_smart_annuity_plus', 'sbilo_smart_annuity_plus',
                'sbilm_smart_swadhan_supreme', 'sbilsa_smart_swadhan_supreme', 'sbilo_smart_swadhan_supreme',
                'sbilm_saral_swadhan_supreme', 'sbilsa_saral_swadhan_supreme', 'sbilo_saral_swadhan_supreme'
            ];

            // Group 2: Lifetime Saver and Money Back
            $group2 = [
                'sbilm_smart_lifetime_saver', 'sbilo_smart_lifetime_saver', 'sbilsa_smart_lifetime_saver', 'sbilpl_smart_lifetime_saver',
                'sbilm_smart_money_back_gold', 'sbilsa_smart_money_back_gold', 'sbilo_smart_money_back_gold'
            ];

            // Group 3: Other plans
            $group3 = [
                'sbilm_saral_swadhan_plus_v3', 'sbilo_saral_swadhan_plus_v3', 'sbilsa_saral_swadhan_plus_v3', 'sbilpl_saral_swadhan_plus_v3',
                'sbilm_smart_wealth_assure', 'sbilo_smart_wealth_assure', 'sbilsa_smart_wealth_assure', 'sbilpl_smart_wealth_assure',
                'sbilm_smart_privilege_v3', 'sbilo_smart_privilege_v3', 'sbilsa_smart_privilege_v3', 'sbilpl_smart_privilege_v3',
                'sbilm_smart_power_insurance', 'sbilo_smart_power_insurance', 'sbilsa_smart_power_insurance', 'sbilpl_smart_power_insurance',
                'sbilm_saral_jeevan_bima', 'sbilo_saral_jeevan_bima', 'sbilsa_saral_jeevan_bima',
                'sbilm_eshield_next', 'sbilsa_eshield_next', 'sbilpl_eshield_next',
                'sbilm_smart_insure_wealth_plus', 'sbilsa_smart_insure_wealth_plus', 'sbilo_smart_insure_wealth_plus',
                'sbilm_smart_money_planner', 'sbilsa_smart_money_planner', 'sbilo_smart_money_planner',
                'sbilm_new_smart_samridhi', 'sbilsa_new_smart_samridhi', 'sbilo_new_smart_samridhi',
                'sbilm_smart_humsafar', 'sbilsa_smart_humsafar', 'sbilo_smart_humsafar',
                'sbilm_saral_insure_wealth_plus', 'sbilsa_saral_insure_wealth_plus', 'sbilo_saral_insure_wealth_plus',
                'sbilm_saral_pension', 'sbilsa_saral_pension', 'sbilo_saral_pension'
            ];
       
    $defaultPdf = 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf';
@endphp

@if (in_array($key, $group1))
    <tr>
        <td colspan="2">
            <span class="bold">
                {{ $key == 'sbilo_retire_smart_plus' ? 'Product Video' : 'FAQs' }} :
            </span>
            @if ($key == 'sbilo_retire_smart_plus')
                <a href="#" target="_blank" title="Product Video">Click Here</a>
            @else
                <a href="{{ $faqs_pdf ?? $defaultPdf }}" target="_blank" title="FAQs">Click Here</a>
            @endif
        </td>
        <td>
            <span class="bold">Sales Brochure : </span>
            <a href="{{ $sales_brocher_pdf ?? $defaultPdf }}" target="_blank" title="Sales Brochure">Click Here</a>
        </td>
    </tr>
    <tr>
        <td>
            <span class="bold">Smart Care : </span>
            <a href="https://smartcare.sbilife.co.in/SmartCare/track-application-otp.html" target="_blank" title="Smart Care">Click Here</a>
        </td>
    </tr>

@elseif (in_array($key, $group2))
    <tr>
        <td>
            <span class="bold">Product Video : </span>
            @if (!empty($prod_Video))
                <a href="{{ $prod_Video }}" target="_blank" title="Product Video">Click Here</a>
            @elseif (str_contains($key, 'smart_lifetime_saver'))
                <a href="https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/smart_lifetime_saver/product_video_english.mp4" target="_blank" title="Product Video">Click Here</a>
            @elseif (str_contains($key, 'smart_money_back_gold'))
                <a href="https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/smart_money_back_gold/product_video_hindi.mp4" target="_blank" title="Product Video">Click Here</a>
            @endif
        </td>
        <td></td>
        <td>
            <span class="bold">Smart Care : </span>
            <a href="https://smartcare.sbilife.co.in/SmartCare/track-application-otp.html" target="_blank" title="Smart Care">Click Here</a>
        </td>
    </tr>
    <tr>
        <td>
            <span class="bold">Sales Brochure : </span>
            <a href="{{ $sales_brocher_pdf ?? $defaultPdf }}" target="_blank" title="Sales Brochure">Click Here</a>
        </td>
        <td></td>
        @if (str_contains($key, 'smart_money_back_gold'))
            <td>
                <span class="bold">FAQs : </span>
                <a href="{{ $faqs_pdf ?? $defaultPdf }}" target="_blank" title="FAQs">Click Here</a>
            </td>
        @endif
    </tr>

@elseif (in_array($key, $group3))
    <tr>
        <td>
            <span class="bold">Sales Brochure : </span>
            <a href="{{ $sales_brocher_pdf ?? $defaultPdf }}" target="_blank" title="Sales Brochure">Click Here</a>
        </td>
    </tr>
@endif


        </table>
    </div>
</body>
</html>


