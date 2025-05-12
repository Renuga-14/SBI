@php use App\Helpers\CommonHelper; @endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SBI LIFE - PIVC Report</title>
    <style>
             .tel { font-family: notosans; }
        .kan { font-family: baloo; }
        .hin,
        .mar,
        .tam,
        .mal,
        .ori,
        .guj,
        .pun,
        .ben,
        .ass,
        .maw { font-family: freeserif; }

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

        .tl { text-align: left !important; }
        .tr { text-align: right !important; }
        .tc { text-align: center !important; }

        .w100p { width: 100%; }
        .w50p { width: 50%; }
        .w25p { width: 25%; }
        .w75p { width: 75%; }

        .wwrap {
            word-wrap: break-word;
            word-break: break-all;
        }

        .bold { font-weight: bold; }
        .pbg1 { background-color: #dde0e6; }
        .vam { vertical-align: middle; }
        .fs24 { font-size: 24px; }
        .uline { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="tc">
                                <img src="https://s3.ap-south-1.amazonaws.com/sbi-prod-data/ap/assets/sbi_logo.jpg" style="width:200px;">
                            </td>
                        </tr>
                        <tr>
                            <td class="tc"><h3>Insta PIV Transcript</h3></td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information fs24">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="fs14">
                                <span class="bold">Proposal No :</span> {{ CommonHelper::check_had_value($link_params['proposal_no']) }}<br>
                                <span class="bold">Name :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['CUSTOMER_NAME']) }}<br>
                                <span class="bold">Product :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['PRODUCT']) }}<br>
                                <span class="bold">Category :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['PRODUCT_CATEGORY']) }}<br>

                                @php
                                    $isAnnuity = strpos(CommonHelper::check_had_value($link_params['flow_data']['PRODUCT']), "Annuity Plus") !== false;
                                    $plan = $isAnnuity || empty($link_params['flow_data']['PLAN'])
                                        ? CommonHelper::AnnuityPlan($link_params['flow_data']['PLAN'], CommonHelper::check_had_value($link_params['flow_data']['PRODUCT_CATEGORY']), $link_details['product_id'])
                                        : '';
                                @endphp

                                <span class="bold">Plan :</span>
                                @if($link_params['flow_key'] == 'sbilo_retire_smart')
                                    <br>
                                @else
                                    {{ CommonHelper::check_had_value($link_params['flow_data']['PLAN']) }}
                                    @if($plan)
                                        ("{{ $plan }}")
                                    @endif
                                    <br>
                                @endif

                                <span class="bold">Premium Amount :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['PREMIUM_AMOUNT']) }}<br>
                                <span class="bold">Sum Assured :</span>
                                @if(!str_starts_with($link_params['proposal_no'], '2R'))
                                    {{ CommonHelper::check_had_value($link_params['flow_data']['SUM_ASSURED']) }}
                                @endif
                                <br>
                                <span class="bold">Frequency :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['FREQUENCY']) }}<br>
                                <span class="bold">Term :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['PAYMENT_TERM']) }}<br>
                                <span class="bold">Source :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['APP_SOURCE']) }}<br>
                                <span class="bold">Channel Name :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['CHANNEL_NAME']) }}<br>
                                <span class="bold">CIF/Agent ID :</span> {{ CommonHelper::check_had_value($link_params['flow_data']['DISTRIBUTOR_ID']) }}<br>
                            </td>

                            <td class="fs14">
                                @if(!empty($device))
                                    <span class="bold">Platform :</span> {{ $device['platform'] }}<br>
                                    <span class="bold">Device Type :</span> {{ $device['device_type'] }}<br>
                                    <span class="bold">Device Name :</span> {{ $device['device'] }}<br>
                                    <span class="bold">Browser :</span> {{ $device['browser'] }}<br>
                                @endif

                                @php $instaProducts = [192, 197]; @endphp
                                <span class="bold">Insta PIV Status :</span>
                                @if(empty($link_details['response']) && in_array($link_details['product_id'], $instaProducts))
                                    Clear Case
                                @else
                                    {{ CommonHelper::pivcRemarks(1, $response) }}
                                @endif
                                <br>

                                <span class="bold">Completed On :</span>
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

// Mapping screen names to short keys
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

// List of flows that should avoid medical questionnaire screens
$retireFlowKeys = [
    'sbilm_retire_smart', 'sbilo_retire_smart', 'sbilsa_retire_smart',
    'sbilpl_retire_smart', 'sbilm_saral_pension', 'sbilsa_saral_pension',
    'sbilpl_saral_pension', 'sbilpl_annuity_plus', 'sbilsa_annuity_plus',
    'sbilo_annuity_plus', 'sbilm_annuity_plus', 'sbilm_retire_smart_plus',
    'sbilo_retire_smart_plus', 'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus'
];

$screenToavoid = in_array($link_params['flow_key'], $retireFlowKeys)
    ? ['Medical Questionnaire', 'Medical Questionnaire - Disagree']
    : [];

// Remove Photo Consent
unset($data_list['Photo Consent']);
@endphp

@if (!empty($data_list))
    @foreach ($data_list as $key => $value)
        @php
            $screen = $value['image']['screen'] ?? '';
            $response = $value['response'] ?? '';
            $mediaUrl = isset($value['image']['media_url']) ? CommonHelper::check_had_value($value['image']['media_url']) : '';
            $mediaScreenUrl = $value['image']['media_screen_url'] ?? '';
            $langClass = $value['image']['language'] ?? 'en';

            // Skip invalid data
            if (empty($screen) || in_array($screen, $screenToavoid) || empty($response)) continue;

            $i++;
            $scnKey = $scrn[$screen] ?? '';
            $score = ($facial && isset($facial[$scnKey])) ? $facial[$scnKey] : 0;
            $audioKey = strtolower(str_replace(' ', '', $screen));
            $audioText = $audio_text[$audioKey] ?? '';
            $audioUrl = url("portal/api/data/playaudioFromPDF/{$link_params['proposal_no']}/$audioKey");
        @endphp

        <tr>
            <td colspan="2">
                <table class="pbg1">
                    <tr class="item">
                        <td colspan="2"><span class="tl bold">Photo {{ $i }}</span></td>
                    </tr>
                    <tr>
                        <td>
                            <img src="{{ $mediaUrl }}" width="210px" height="280px" />
                            @if ($audioKey !== 'productbenefits')
                                <table>
                                    <tr class="item">
                                        <td colspan="2">
                                            <span class="tl">Audio</span>
                                            <p style="padding:15px;">
                                                <a href="{{ $audioUrl }}" target="_blank">
                                                    <img src="https://cdn.iconscout.com/icon/free/png-256/speaker-2653706-2202518.png" width="20px" height="20px" />
                                                </a>
                                            </p>
                                            <br>
                                            <span class="t1 {{ $langClass }}">{{ $audioText }}</span>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        </td>
                        <td style="text-align:left;">
                            <img src="{{ $mediaScreenUrl }}" width="250px" height="480px" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
@endif
@php
    $flow_key = $link_params['flow_key'] ?? '';

    $retire_products = [
        'sbilm_retire_smart_plus', 'sbilo_retire_smart_plus', 'sbilsa_retire_smart_plus', 'sbilpl_retire_smart_plus',
        'sbilm_smart_annuity_plus', 'sbilsa_smart_annuity_plus', 'sbilo_smart_annuity_plus',
        'sbilm_smart_swadhan_supreme', 'sbilsa_smart_swadhan_supreme', 'sbilo_smart_swadhan_supreme',
        'sbilm_saral_swadhan_supreme', 'sbilsa_saral_swadhan_supreme', 'sbilo_saral_swadhan_supreme'
    ];

    $lifetime_saver_products = [
        'sbilm_smart_lifetime_saver', 'sbilo_smart_lifetime_saver', 'sbilsa_smart_lifetime_saver', 'sbilpl_smart_lifetime_saver',
        'sbilm_smart_money_back_gold', 'sbilsa_smart_money_back_gold', 'sbilo_smart_money_back_gold'
    ];

    $other_products = [
        'sbilm_saral_swadhan_plus_v3', 'sbilo_saral_swadhan_plus_v3', 'sbilsa_saral_swadhan_plus_v3', 'sbilpl_saral_swadhan_plus_v3',
        'sbilm_smart_wealth_assure', 'sbilo_smart_wealth_assure', 'sbilsa_smart_wealth_assure', 'sbilpl_smart_wealth_assure',
        'sbilm_smart_privilege_v3', 'sbilo_smart_privilege_v3', 'sbilsa_smart_privilege_v3', 'sbilpl_smart_privilege_v3',
        'sbilm_smart_power_insurance', 'sbilpl_smart_power_insurance', 'sbilo_smart_power_insurance', 'sbilsa_smart_power_insurance',
        'sbilm_saral_jeevan_bima', 'sbilo_saral_jeevan_bima', 'sbilsa_saral_jeevan_bima',
        'sbilm_eshield_next', 'sbilsa_eshield_next', 'sbilpl_eshield_next',
        'sbilm_smart_insure_wealth_plus', 'sbilsa_smart_insure_wealth_plus', 'sbilo_smart_insure_wealth_plus',
        'sbilm_smart_money_planner', 'sbilsa_smart_money_planner', 'sbilo_smart_money_planner',
        'sbilm_new_smart_samridhi', 'sbilsa_new_smart_samridhi', 'sbilo_new_smart_samridhi',
        'sbilm_smart_humsafar', 'sbilsa_smart_humsafar', 'sbilo_smart_humsafar',
        'sbilm_saral_insure_wealth_plus', 'sbilsa_saral_insure_wealth_plus', 'sbilo_saral_insure_wealth_plus',
        'sbilm_saral_pension', 'sbilsa_saral_pension', 'sbilo_saral_pension'
    ];
@endphp

<tr>
    <td colspan="2">
        <table class="pbg1">
            <tr class="item">
                <td colspan="3"><span class="tl bold">Thank You</span></td>
            </tr>

            {{-- Retire Products --}}
            @if(in_array($flow_key, $retire_products))
                <tr>
                    <td colspan="2">
                        @if($flow_key === 'sbilo_retire_smart_plus')
                            <span class="bold">Product Video: </span>
                            <a href="#" target="_blank" title="Product Video">Click Here</a>
                        @else
                            <span class="bold">FAQs: </span>
                            <a href="{{ $faqs_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank" title="FAQs">Click Here</a>
                        @endif
                    </td>
                    <td>
                        <span class="bold">Sales Brochure: </span>
                        <a href="{{ $sales_brocher_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank" title="Sales Brochure">Click Here</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="bold">Smart Care: </span>
                        <a href="https://smartcare.sbilife.co.in/SmartCare/track-application-otp.html" target="_blank">Click Here</a>
                    </td>
                </tr>

            {{-- Lifetime Saver Products --}}
            @elseif(in_array($flow_key, $lifetime_saver_products))
                <tr>
                    <td>
                        <span class="bold">Product Video: </span>
                        @if(in_array($flow_key, ['sbilm_smart_lifetime_saver', 'sbilo_smart_lifetime_saver', 'sbilsa_smart_lifetime_saver', 'sbilpl_smart_lifetime_saver']))
                            <a href="https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/smart_lifetime_saver/product_video_english.mp4" target="_blank">Click Here</a>
                        @elseif(in_array($flow_key, ['sbilm_smart_money_back_gold', 'sbilsa_smart_money_back_gold', 'sbilo_smart_money_back_gold']))
                            <a href="{{ $prod_Video ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/smart_money_back_gold/product_video_hindi.mp4' }}" target="_blank">Click Here</a>
                        @else
                            <a href="https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/product_video_english.mp4" target="_blank">Click Here</a>
                        @endif
                    </td>
                    <td></td>
                    <td>
                        <span class="bold">Smart Care: </span>
                        <a href="https://smartcare.sbilife.co.in/SmartCare/track-application-otp.html" target="_blank">Click Here</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="bold">Sales Brochure: </span>
                        <a href="{{ $sales_brocher_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank">Click Here</a>
                    </td>
                    <td></td>
                    @if(in_array($flow_key, ['sbilm_smart_money_back_gold', 'sbilsa_smart_money_back_gold', 'sbilo_smart_money_back_gold']))
                        <td>
                            <span class="bold">FAQs: </span>
                            <a href="{{ $faqs_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank">Click Here</a>
                        </td>
                    @endif
                </tr>

            {{-- Other Products --}}
            @elseif(in_array($flow_key, $other_products))
                <tr>
                    <td>
                        <span class="bold">Sales Brochure: </span>
                        <a href="{{ $sales_brocher_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank">Click Here</a>
                    </td>
                    <td>
                        <span class="bold">Smart Care: </span>
                        <a href="https://smartcare.sbilife.co.in/SmartCare/track-application-otp.html" target="_blank">Click Here</a>
                    </td>
                    <td>
                        <span class="bold">FAQs: </span>
                        <a href="{{ $faqs_pdf ?? 'https://sbi-prod-data.s3.ap-south-1.amazonaws.com/adc/product_repo/retire_smart/brochure.pdf' }}" target="_blank">Click Here</a>
                    </td>
                </tr>
            @endif
<tr>
    <td>
        <span class="bold">Agent Name: </span>{{ CommonHelper::check_had_value($link_params['flow_data']['CD_NAME'] ?? '') }}
    </td>
    <td></td>
    <td>
        <span class="bold">Agent Mobile No: </span>
        <a href="tel:{{ CommonHelper::check_had_value($link_params['flow_data']['CD_MOBILE_NO'] ?? '') }}" target="_blank">
            {{ CommonHelper::check_had_value($link_params['flow_data']['CD_MOBILE_NO'] ?? '') }}
        </a>
    </td>
</tr>
<tr>
    <td>
        <span class="bold">Toll Free: </span>
        <a href="tel:18002679090" target="_blank">18002679090</a><br>
    </td>
    <td style="text-align: left">
        <span class="bold">Email: </span>
        <a href="mailto:info@sbilife.co.in" target="_blank">info@sbilife.co.in</a><br>
    </td>
    <td>
        <span class="bold">Website: </span>
        <a href="https://www.sbilife.co.in" target="_blank" title="FAQs">https://www.sbilife.co.in</a><br>
    </td>
</tr>
        </table>
    </td>
</tr>


        </table>
    </div>
</body>
</html>
