<?php

namespace App\Http\Controllers;

use App\Models\Affiliator;
use App\Models\Reseller;
use Illuminate\Http\Request;

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

        return view('partners.index', compact('types', 'resellers', 'affiliates'));
    }
}
