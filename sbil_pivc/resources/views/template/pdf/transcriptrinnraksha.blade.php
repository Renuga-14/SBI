<?php

use App\Helpers\CommonHelper;
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>SBI LIFE - PIVC Report</title>


    <style>
        .hin,
        .mar,
        .pun,
        .tam,
        .mal,
        .ass,
        .ori {
            font-family: freeserif;
        }

        .kan { font-family: 'kan'; }
        .tel { font-family: 'tel'; }
        .maw { font-family: 'maw'; }
        .ben { font-family: 'ben'; }
        .guj { font-family: 'guj'; }





        .english-font {
            font-family: 'Arial', sans-serif;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 30px;
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


        .title{
            text-align: center;
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

        /* .page-break {
            page-break-before: always;
        } */
        .page-break {
            page-break-after: always;
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
                            <td class="title">
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

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="font-size: 14px;">
                                <span class="bold">Loan Account Number:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['LOANACCOUNTNUMBER']) ?? '-' }}<br>
                                <span class="bold">Form Number:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['FORMNUMBER']) ?? '-' }}<br>
                                <span class="bold">Name:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['POLICY_HOLDER_NAME']) ?? '-' }}<br>
                                <span class="bold">Loan Category:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['LOAN_CATEGORY']) ?? '-' }}<br>
                                <span class="bold">Source:</span> {{ CommonHelper::check_had_value(strtoupper($link_params['source'])) }}<br>
                                <span class="bold">Master Policy Holder Name:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['MASTER_POLICY_HOLDER_NAME']) ?? '-' }}<br>
                                <span class="bold">Branch Name:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['BRANCH_NAME']) ?? '-' }}<br>
                                <span class="bold">Branch Code:</span> {{ CommonHelper::check_had_value($link_params['flow_data']['BRANCH_CODE']) ?? '-' }}<br>
                            </td>
                            <td style="font-size: 14px;">
                                @if(!empty($device))
                                <span class="bold">Platform:</span> {{ $device['platform'] }}<br>
                                <span class="bold">Device Type:</span> {{ $device['device_type'] }}<br>
                                <span class="bold">Device Name:</span> {{ $device['device'] }}<br>
                                <span class="bold">Browser:</span> {{ $device['browser'] }}<br>
                                @endif
                                @php
                                $statusremark = CommonHelper::pivcRinnRakshaRemarks(1, $link_details['disagree_status'], $response, $link_details['completed_on']);
                                @endphp
                                <span class="bold">Insta PIV Status:</span> {{ $statusremark['mainreason'] ?? '' }}<br>
                                <span class="bold">Completed On:</span>
                                {{ CommonHelper::check_had_value($link_details['completed_on']) ? CommonHelper::date_convert($link_details['completed_on'], 'd-M-Y, h:i:s A') : '' }}

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @php $photoIndex = 1; @endphp

            @foreach($data_list as $index => $data)

            @if(!empty($data['image']))
            @php

            $screen = $data['image']['screen'];
            $screenKey = strtolower(str_replace(" ", "", $screen));
            $langClass = $data['image']['language'] ?? '';
            $audioTextValue = $audio_text[$screenKey] ?? '';


           // $audioTextValue = mb_convert_encoding($audioTextValue, 'UTF-8', 'auto');


            $agree = $data['response']['agree_status']?? null;
            $isMedical = in_array($screen, ['Medical Confirmation Screen One', 'Medical Confirmation Screen Two']);
            $agreeText = $agree ? 'Yes' : 'No';
            $color = ($agreeText === 'Yes') ? ($isMedical ? 'red' : 'green') : ($isMedical ? 'green' : 'red');
            @endphp

            <tr>
                <td colspan="2">
                    <table class="pbg1">
                        <tr>
                            <td colspan="2"><span class="bold">Photo {{ $photoIndex }} </span></td>
                        </tr>
                        <tr>
                            <td>
                                <img src="{{ $data['image']['media_url'] }}" width="210px" height="280px" />
                                <table>
                                    <tr>
                                        <td>
                                            @if(in_array($screen, ['Personal Details', 'Personal Details - Disagree', 'Medical Questionnaire', 'Medical Questionnaire - Disagree','Welcome Screen']))
                                            <span class="tl">Audio
                                                <p style="padding:15px;">
                                                    <a href="{{ url('api/data/playAudioFromPDF/' . $link_params['proposal_no'] . '/' . str_replace(' ', '', strtolower($screen))) }}" target="_blank">
                                                        <img src="https://cdn.iconscout.com/icon/free/png-256/speaker-2653706-2202518.png" width="20px" height="20px">
                                                    </a>

                                                </p>
                                            </span>
                                            @endif

                                            @if(!empty($audioTextValue))

                                            <span class="tl {{ $langClass }}">{!! $audioTextValue !!}</span>

                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td style="text-align:left;">
                                <img src="{{ $data['image']['media_screen_url'] }}" width="250px" height="480px" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @if(!empty($data['response']))
            <tr>
                <td colspan="2">
                    <table class="pbg1">
                        <tr>
                            <td colspan="2"><span class="bold">Responses</span></td>
                        </tr>
                        <tr>
                            <td>
                                <span class="bold" style="color:{{ $color }}">Agreement Status: </span>
                                <span style="color:{{ $color }}">{{ $agreeText }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="page-break"></div>
                </td>
            </tr>
            @endif
            <br>
            <div class="page-break"></div>

            @php $photoIndex++; @endphp

            @endif
<!--vikram--->




            @endforeach
 <tr>
            <td colspan="2">
                <table class="pbg1">
                    <tr class="item">
                        <td colspan="3">
                            <span class="tl bold">Thank You</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="bold">Sales Brochure: </span>
                            <a href="{{ asset('https://cloud-rnd-data.s3.ap-south-1.amazonaws.com/rinraksha/brochure.pdf') }}" target="_blank" title="Sales Brochure">Click Here</a>
                        </td>
                        <td>
                            {{-- Smart Care link removed/commented --}}
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

