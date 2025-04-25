<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
class CommonHelper
{
    const ENCRYPT_METHOD = 'AES-256-CBC';
    const DEFAULT_ENCRYPT_KEY = 'TUB5am9ASm1hJDIwMTk'; // Set your secret key
    const DEFAULT_ENCRYPT_VKEY = 'Sk1BQEFuVG9ueSQyMDE5'; // Set your IV key

    /**
     * Check if required POST parameters are null or empty.
     */
    public static function checkPostParamNull(Request $request, array $params = []): bool
    {
        if (empty($params)) {
            return false;
        }

        foreach ($params as $param) {
            if (!$request->has($param) || empty($request->input($param))) {
                return false;
            }
        }

        return true;
    }

    public static function decryptString(string $string): ?string
{
    try {
        return Crypt::decryptString($string);
    } catch (\Exception $e) {
        return "Decryption failed: " . $e->getMessage();
    }
}




    public static function encryptString(string $string): ?string
    {
        if (empty($string)) {
            return null;
        }

        // Generate a 32-byte key and 16-byte IV
        $key = substr(hash('sha256', env('DEFAULT_ENCRYPT_KEY', 'fallback_key'), true), 0, 32);
        $iv = substr(hash('sha256', env('DEFAULT_ENCRYPT_VKEY', 'fallback_iv'), true), 0, 16);

        // Encrypt the data
        $encrypted = openssl_encrypt($string, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);

    }



    public static function validateMobApp($headerValue)
    {
        $allowedValues = ['mobile-app', 'android-app', 'ios-app'];
        return in_array(strtolower($headerValue), $allowedValues);
    }

    public static function fileNameStd($str, $replace = '_', $upper = true)
    {
        if (!empty($str)) {
            $str = trim($str);
            $str = $upper ? strtoupper($str) : strtolower($str);
            return preg_replace('/\s+/', $replace, $str);
        }

        return '';
    }
    public static function makeDirs($dirPath, $mode = 0777)
    {
        return is_dir($dirPath) || mkdir($dirPath, $mode, true);
    }
    public static function localFileDelete($path)
    {
        if (!empty($path)) {
            if (Storage::exists($path)) {
                return Storage::delete($path);
            }
        }
        return false;
    }
    public static function pivcRemarks($disStatus, $resArr)
    {
        $arr = ['ePerDet', 'ePolDet', 'eMedQuest', 'eBenIll', 'eProdBenef', 'eSmsOtp'];
        $ret = 'Clear Case';

        if ($disStatus) {
            if (!empty($resArr)) {
                $list = [];

                foreach ($arr as $vR) {
                    if (isset($resArr[$vR])) {
                        $list[] = $vR;
                    }
                }

                if (count($list) === 1 && in_array('ePerDet', $list)) {
                    $ret = "Major Correction";
                } elseif (count($list) === 1 && in_array('eMedQuest', $list)) {
                    $ret = "Medical Dispute";
                } elseif (count($list) === 2 && in_array('ePerDet', $list) && in_array('eMedQuest', $list)) {
                    $ret = "Medical Dispute";
                } elseif (count($list) >= 1 && in_array('eMedQuest', $list) && in_array('eSmsOtp', $list)) {
                    $ret = "Medical Dispute";
                } elseif (count($list) >= 1) {
                    $ret = "Mismatch";
                }
            }
        }

        return $ret;
    }
    public static function pivcRinnRakshaRemarks($completeStatus, $disStatus, $resArr, $date)
    {
        $rinnrak = [];
        $arr = ['ePerDet', 'eMedQuest', 'eSmsOtp', 'eMedicalQuestionOne', 'eMedicalQuestionTwo'];

        if ($completeStatus) {
            if ($disStatus) {
                if (!empty($resArr)) {
                    $list = [];
                    foreach ($arr as $vR) {
                        if (isset($resArr[$vR])) {
                            $list[] = $vR;
                        }
                    }

                    if (count($list) == 1 && in_array('ePerDet', $list)) {
                        $rinnrak = [
                            'piwc_call_flag' => 'Y',
                            'precalling' => 'Y',
                            'piwc_med_flag' => 'N',
                            'mainreason' => 'CLEAR CASE',
                            'sub_reason' => 'MAJOR CORRECTION',
                            'remarks' => "PIV Clear Insta Completed on " . $date
                        ];
                    } elseif (!in_array('ePerDet', $list) && count($list) >= 1) {
                        $rinnrak = [
                            'piwc_call_flag' => 'Y',
                            'precalling' => 'Y',
                            'piwc_med_flag' => 'Y',
                            'mainreason' => 'MEDICAL DISPUTE',
                            'sub_reason' => 'NA',
                            'remarks' => "PIV Clear Insta Completed on " . $date
                        ];
                    } elseif (count($list) > 1) {
                        $rinnrak = [
                            'piwc_call_flag' => 'Y',
                            'precalling' => 'Y',
                            'piwc_med_flag' => 'Y',
                            'mainreason' => 'MEDICAL DISPUTE',
                            'sub_reason' => 'MAJOR CORRECTION',
                            'remarks' => "PIV Clear Insta Completed on " . $date
                        ];
                    }
                }
            } else {
                $rinnrak = [
                    'piwc_call_flag' => 'Y',
                    'precalling' => 'Y',
                    'piwc_med_flag' => 'N',
                    'mainreason' => 'CLEAR CASE',
                    'sub_reason' => 'NA',
                    'remarks' => "PIV Clear Insta Completed on " . $date
                ];
            }
        } else {
            $rinnrak = [
                'piwc_call_flag' => 'Y',
                'precalling' => 'Y',
                'piwc_med_flag' => 'N',
                'mainreason' => 'Clear Case',
                'sub_reason' => 'NA',
                'remarks' => "PIV Clear Insta Completed on " . $date
            ];
        }

        return $rinnrak;
    }
    public static function pivcFullRemarkStatus($completeStatus, $disStatus, array $resArr)
    {
        $result = '';
        $expectedKeys = ['ePerDet', 'ePolDet', 'eMedQuest', 'eBenIll', 'eProdBenef', 'eSmsOtp'];

        if ($completeStatus) {
            if (!empty($resArr)) {
                $matchedKeys = [];

                foreach ($expectedKeys as $key) {
                    if (array_key_exists($key, $resArr)) {
                        $matchedKeys[] = $key;
                    }
                }

                $count = count($matchedKeys);

                if ($count === 1 && in_array('ePerDet', $matchedKeys)) {
                    $result = 'Y';
                } elseif ($count === 1 && in_array('eMedQuest', $matchedKeys)) {
                    $result = 'M';
                } elseif ($count === 2 && in_array('ePerDet', $matchedKeys) && in_array('eMedQuest', $matchedKeys)) {
                    $result = 'M';
                } elseif ($count === 2 && in_array('eMedQuest', $matchedKeys) && in_array('eSmsOtp', $matchedKeys)) {
                    $result = 'M';
                } elseif ($count >= 1) {
                    $result = 'N';
                } else {
                    $result = 'Y';
                }
            }
        } else {
            $result = 'N';
        }

        return $result;
    }

    public static function pivcFullRemarks($completeStatus, $disStatus, array $resArr)
    {
        $result = '';
        $expectedKeys = ['ePerDet', 'ePolDet', 'eMedQuest', 'eBenIll', 'eProdBenef', 'eSmsOtp'];

        if ($completeStatus) {
            if (!empty($resArr)) {
                $matchedKeys = [];

                foreach ($expectedKeys as $key) {
                    if (isset($resArr[$key])) {
                        $matchedKeys[] = $key;
                    }
                }

                $count = count($matchedKeys);

                if ($count === 1 && in_array('ePerDet', $matchedKeys)) {
                    $result = 'Major Correction';
                } elseif ($count === 1 && in_array('eMedQuest', $matchedKeys)) {
                    $result = 'Medical Dispute';
                } elseif ($count === 2 && in_array('ePerDet', $matchedKeys) && in_array('eMedQuest', $matchedKeys)) {
                    $result = 'Medical Dispute';
                } elseif ($count >= 1 && in_array('eMedQuest', $matchedKeys) && in_array('eSmsOtp', $matchedKeys)) {
                    $result = 'Medical Dispute';
                } elseif ($count >= 1) {
                    $result = 'Mismatch';
                } else {
                    $result = 'Clear Case';
                }

                unset($matchedKeys);
            }
        } else {
            $result = 'Customer not available';
        }

        return $result;
    }

    public static function check_had_value($val, $return = null) {
       // print_r($val);die;
        return (isset($val) && $val !== '') ? trim($val) : $return;
    }

    public static function check_boolean_value($val,$true=TRUE,$false=FALSE)
    {
        return ($val)? $true : $false;
    }

    public static function formatDisagreementInput($val,$return=NULL)
    {
        return (isset($val) && ($val!=''))? lcfirst(ltrim($val,'in_')) : $return;
    }


    public static function date_convert($date, $format = 'd-m-Y H:i:s')
    {
        if ($date) {
            return Carbon::parse($date)->format($format);
        }

        return null;
    }


}
