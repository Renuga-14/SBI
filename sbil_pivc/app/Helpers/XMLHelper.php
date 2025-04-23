<?php
namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class XMLHelper
{
    public static function parseRinnRikshaPIVCXml($xml_data)
    {
        $xml_arr = XMLHelper::xmlToArray($xml_data);
        return ($xml_arr === false) ? false : XMLHelper::formatArrayRinnRiksha($xml_arr);
    }




    public static function xmlToArray($xml_data)
    {
        try {
            $xml = simplexml_load_string($xml_data, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            return json_decode($json, true);
        } catch (\Exception $e) {
            Log::error('XML Parsing Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function formatArrayRinnRiksha($xml_arr)
    {
        if (!empty($xml_arr)) {
            $res_arr = [];

            foreach ($xml_arr as $maKey => $maValue) {
                foreach ($maValue as $mk => $mv) {
                    $maTempValue = self::filterXMLArrayValue($mv);

                     if ($maKey == 'GENDER') {
                        $res_arr['MA_SALUTATION'] = ($maTempValue == 'F') ? 'Mrs.' : 'Mr.';
                        $res_arr['MA_GENDER'] = ($maTempValue == 'F') ? 'Female' : (($maTempValue == 'M') ? 'Male' : '');
                    }

                    $res_arr[$mk] = $maTempValue;
                }
            }

            return $res_arr;
        }

        return false;
    }

    public static function filterXMLArrayValue($val)
    {
        if (is_array($val)) {
            $temp = array_filter($val, function ($item) {
                return is_array($item) ? array_filter($item) : !empty($item);
            });

            return !empty($temp) ? $temp : null;
        }

        return !empty($val) ? $val : null;
    }

    public static function parsePIVCXml(string $xmlData): array|bool
    {
        $xmlArr = XMLHelper::xmlToArray($xmlData); // Use self::

        if ($xmlArr === false) {
            return false;
        }

        // If 'Table' key exists
        if (isset($xmlArr['Table'])) {
            $table = $xmlArr['Table'];
            $tableCount = is_array($table) ? count($table) : 0;
          //  print_r(  $table );die;
            // More than 2 tables — format as one
            if ($tableCount > 2) {

                return XMLHelper::formatArray($xmlArr); // Use self::

            }

            // 1 or 2 tables — format each individually
            if ($tableCount > 0) {
                $products = [];
                //print_r(  $products );die;
                foreach ($table as $row) {
                    $products[] = XMLHelper::formatArray(['Table' => $row]); // Use self::
                }

                return $products;
            }
        }

        // Default formatting if no 'Table' or fallback
        return XMLHelper::formatArray($xmlArr); // Use self::
    }



    public static function formatArray(array $xmlArr): array|bool
{
    $mainArr = $xmlArr['Table'] ?? [];

    if (!empty($mainArr)) {
        $resArr = [];

        foreach ($mainArr as $key => $value) {
            $tempValue = self::filterXMLArrayValue($value);

            if ($key === 'DOB_PH') {


            $resArr['MA_' . $key] = $tempValue ? Carbon::parse($tempValue)->format('d-m-Y') : null;

            }

            if ($key === 'GENDER') {
                $resArr['MA_SALUTATION'] = $tempValue === 'F' ? 'Mrs.' : 'Mr.';
                $resArr['MA_GENDER'] = $tempValue === 'F' ? 'Female' : ($tempValue === 'M' ? 'Male' : '');
            }

            $resArr[$key] = $tempValue;

            if ($key === 'PRODUCT_CATEGORY' && isset($mainArr['SOURCE'], $mainArr['UIN_NO'])) {
                if (strtolower($mainArr['SOURCE']) === "online" &&
                    in_array($mainArr['UIN_NO'], ["111L100V02", "111L100V03"])) {
                    $resArr['PRODUCT_CATEGORY'] = "ULIP";
                }
            }
        }

        return $resArr;
    }

    return false;
}


}
