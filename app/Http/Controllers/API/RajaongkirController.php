<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaongkirController extends Controller
{
    /*
     * List of provinces
     */
    public function provinces()
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        $result = data_get($response->json(), 'data');

        return response()->json([
            'data' => $result
        ]);
    }

    /*
     * List of cities
     */
    public function cities($id)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/city/' . $id);

        $result = data_get($response->json(), 'data');

        return response()->json([
            'data' => $result
        ]);
    }

    /*
     * List of districts
     */
    public function districts($id)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/district/' . $id);

        $result = data_get($response->json(), 'data');

        return response()->json([
            'data' => $result
        ]);
    }

    /*
     * List of subdistricts
     */
    public function subdistricts($id)
    {
        $response = Http::withHeaders([
            'key' => env('RAJAONGKIR_API_KEY')
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/sub-district/' . $id);

        $result = data_get($response->json(), 'data');

        return response()->json([
            'data' => $result
        ]);
    }

    /*
     * Calculate cost
     */
    public function calculateCost($destination, $weight, $courier)
    {
        $response = Http::asForm()->withHeaders([
            'accept' => 'application/json',
            'key' => env('RAJAONGKIR_API_KEY'),
        ])->post('https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost', [
            "origin" => 2163,
            "destination" => $destination,
            "weight" => $weight,
            "courier" => $courier
        ]);

        $result = data_get($response->json(), 'data');

        return response()->json([
            'data' => $result
        ]);
    }
}
