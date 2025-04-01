<?php
namespace App\Repositories;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
// use App\Models\Link;
use Illuminate\Database\Eloquent\Model;

class CommonRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function select(
        string $returnType = 'result_array',
        array $select = ['*'],
        string $table,
        array $where = [],
        string $sort = null,
        int $limit = null,
        int $offset = 0
    ) {

        $query = \DB::table($table)->select($select);

        if (!empty($where)) {
            $query->where($where);
        }

        if (!empty($sort)) {
            $query->orderBy($sort);
        }

        if (!empty($limit)) {
            $query->limit($limit)->offset($offset);
        }

        $result = $query->get();

        return ($returnType === 'result_array') ? $result->toArray() : $result;
    }

    public function sbilShortUrl($url)
    {
        $params = [
            'Modulename' => config('constants.SBIL_APIP_NAME'),
            'Url' => $url,
            'ExpiryStatus' => 'N'
        ];

        // Send POST request using Laravel HTTP Client
        $response = Http::withHeaders([
            'x-ibm-client-id' => config('constants.SBIL_APIP_CLIENT'),
            'x-ibm-client-secret' => config('constants.SBIL_APIP_SECRET'),
            'Content-Type' => 'application/json'
        ])->post(config('constants.SBIL_SHORT_URL'), $params);

        // Decode JSON response
        $resArr = $response->json();

        // Check if API returned a valid result
        if (!empty($resArr['Result'])) {
            if (!filter_var($resArr['Result'], FILTER_VALIDATE_URL)) {
                Log::error('KFD -- sbilShortUrl -- FAILED', [
                    'POST_PARAMS' => $params,
                    'OUTPUT' => $resArr
                ]);
                return false;
            } else {
                Log::info('KFD -- sbilShortUrl', [
                    'POST_PARAMS' => $params,
                    'OUTPUT' => $resArr
                ]);
                return $resArr['Result'];
            }
        } else {
            Log::error('KFD -- sbilShortUrl -- FAILED', [
                'POST_PARAMS' => $params,
                'OUTPUT' => $resArr
            ]);
            return false;
        }
    }

    public function insert($table, $insertData, $returnType = 'boolean')
    {

        $inserted = \DB::table($table)->insertGetId($insertData);

        return ($returnType === 'id') ? $inserted : (bool) $inserted;
    }
    public function updateRecord($table, $arrWhere, $arrayData)
        {
            if (!empty($arrayData)) {
                $affectedRows = DB::table($table)->where($arrWhere)->update($arrayData);

                return $affectedRows > 0 ? $affectedRows : false;
            }

            return false;
        }
     


}


