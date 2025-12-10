<?php

namespace App\Http\Controllers;

use App\Models\Affiliator;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PartnerController extends Controller
{

    public function index()
    {
        $types = [
            ['id' => 1, 'type' => 'reseller'],
            ['id' => 2, 'type' => 'affiliate'],
        ];

        $resellers = Reseller::orderBy('id', 'desc')->get();
        $affiliates = Affiliator::orderBy('id', 'desc')->get();

        /*
    |--------------------------------------------------------------------------
    | PROVINCE
    |--------------------------------------------------------------------------
    */
        try {
            $provinces = Cache::remember('rajaongkir_provinces', 86400, function () {
                return Http::withHeaders([
                    'key' => env('RAJAONGKIR_API_KEY')
                ])->timeout(10)
                    ->get('https://rajaongkir.komerce.id/api/v1/destination/province')
                    ->json('data');
            });
        } catch (\Exception $e) {
            Log::error('Province API error: ' . $e->getMessage());
            $provinces = [];
        }

        $provinceMap = collect($provinces)->pluck('name', 'id');

        /*
    |--------------------------------------------------------------------------
    | CITY
    |--------------------------------------------------------------------------
    */
        $cityMap = [];
        foreach ($resellers->groupBy('province_id') as $provinceId => $items) {
            try {
                $cities = Cache::remember("rajaongkir_cities_$provinceId", 86400, function () use ($provinceId) {
                    return Http::withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->timeout(10)
                        ->get("https://rajaongkir.komerce.id/api/v1/destination/city/$provinceId")
                        ->json('data');
                });

                foreach ($cities as $city) {
                    $cityMap[$city['id']] = $city['name'];
                }
            } catch (\Exception $e) {
                Log::error("City API error ($provinceId): " . $e->getMessage());
            }
        }

        /*
    |--------------------------------------------------------------------------
    | DISTRICT
    |--------------------------------------------------------------------------
    */
        $districtMap = [];
        foreach ($resellers->groupBy('city_id') as $cityId => $items) {
            try {
                $districts = Cache::remember("rajaongkir_districts_$cityId", 86400, function () use ($cityId) {
                    return Http::withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->timeout(10)
                        ->get("https://rajaongkir.komerce.id/api/v1/destination/district/$cityId")
                        ->json('data');
                });

                foreach ($districts as $district) {
                    $districtMap[$district['id']] = $district['name'];
                }
            } catch (\Exception $e) {
                Log::error("District API error ($cityId): " . $e->getMessage());
            }
        }

        /*
    |--------------------------------------------------------------------------
    | INJECT NAME
    |--------------------------------------------------------------------------
    */
        $resellers = $resellers->map(function ($item) use ($provinceMap, $cityMap, $districtMap) {

            $item->province_name = $provinceMap[$item->province_id] ?? 'UNKNOWN';
            $item->city_name = $cityMap[$item->city_id] ?? 'UNKNOWN';
            $item->district_name = $districtMap[$item->district_id] ?? 'UNKNOWN';

            return $item;
        });

        return view('partners.index', compact('types', 'resellers', 'affiliates'));
    }
}
