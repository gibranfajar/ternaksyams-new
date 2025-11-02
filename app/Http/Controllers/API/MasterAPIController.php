<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\BenefitResource;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PromotionResource;
use App\Http\Resources\TutorialResource;
use App\Http\Resources\VariantResource;
use App\Models\About;
use App\Models\Affiliator;
use App\Models\Article;
use App\Models\Benefit;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Reseller;
use App\Models\Tutorial;
use App\Models\User;
use App\Models\Variant;
use App\Models\Voucher;
use App\Models\VoucherProduct;
use App\Models\VoucherUsage;
use App\Models\VoucherUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasterAPIController extends Controller
{
    /**
     * Get User Info Authentication
     */
    public function getUser()
    {
        $user = Auth::user();
        $data = User::with('profile')->where('id', $user->id)->first();

        $json = [
            'id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'email_verified_at' => $data->email_verified_at,
            'role' => $data->role,
            'google_id' => $data->google_id,
            'whatsapp' => $data->profile?->whatsapp,
            'address' => $data->address,
            'province' => $data->profile?->province,
            'city' => $data->profile?->city,
            'district' => $data->profile?->district,
            'postal_code' => $data->profile?->postal_code,
        ];

        return response()->json($json, 200);
    }


    /*
     * Create Resellers Account
     */
    public function createReseller(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'whatsapp' => 'required',
            'email' => 'required',
            'address' => 'required',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'postal_code' => 'required',
            'bank' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
        ]);

        try {
            $reseller = Reseller::create($request->all());
            return response()->json(['message' => 'Reseller created successfully', 'reseller' => $reseller], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create reseller'], 500);
        }
    }

    /*
     * Create Affiliate Account
     */
    public function createAffiliate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'whatsapp' => 'required',
            'email' => 'required',
            'province' => 'required',
            'city' => 'required',
            'sosmed_account' => 'nullable',
            'shopee_account' => 'nullable',
            'tokopedia_account' => 'nullable',
            'tiktok_account' => 'nullable',
            'lazada_account' => 'nullable',
        ]);

        try {
            $affiliate = Affiliator::create($request->all());
            return response()->json(['message' => 'Affiliate created successfully', 'affiliate' => $affiliate], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create affiliate'], 500);
        }
    }

    /*
     * Get All Categories
     */
    public function categories()
    {
        $categories = Category::all();
        return response()->json(['data' => $categories], 200);
    }

    /*
     * Get All Products
     */
    public function products()
    {
        $variants = Variant::with('sizes')->orderBy('id', 'desc')->get();
        $data = ProductResource::collection($variants);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get Detail Product
     */
    public function detailProduct($slug)
    {
        $variants = Variant::with(['sizes', 'flavour', 'product'])->where('slug', $slug)->get();
        $data = ProductDetailResource::collection($variants);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get All Variants
     */
    public function variants()
    {
        $products = Product::with('variants.sizes.size', 'variants.images')
            ->orderBy('id', 'desc')
            ->get();

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'description' => $product->description,
                'image' => $product->variants->first()?->images->first()
                    ? asset('storage/' . $product->variants->first()->images->first()->image_path)
                    : null, // thumbnail utama dari variant pertama
                'sizes' => $product->variants->first()?->sizes->map(function ($vs) {
                    return $vs->size?->label . ' ' . $vs->size?->unit;
                }),
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id'    => $variant->id,
                        'name'  => $variant->name,
                        'image' => $variant->images->first()
                            ? asset('storage/' . $variant->images->first()->image_path)
                            : null
                    ];
                }),
            ];
        }

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get All Articles
     */
    public function articles()
    {
        $articles = Article::with('category')->orderBy('id', 'desc')->get();
        $data = ArticleResource::collection($articles);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get Detail Article
     */
    public function detailArticle($slug)
    {
        $article = Article::with('category')->where('slug', $slug)->first();
        $data = new ArticleResource($article);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get All Tutorials
     */
    public function tutorials()
    {
        $tutorials = Tutorial::with('category')->orderBy('id', 'desc')->get();
        $data = TutorialResource::collection($tutorials);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get Benefits Resellers
     */
    public function benefitResellers()
    {
        $benefits = Benefit::where('type', 'reseller')->get();
        $data = BenefitResource::collection($benefits);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get Benefits Affiliates
     */
    public function benefitAffiliates()
    {
        $benefits = Benefit::where('type', 'affiliate')->get();
        $data = BenefitResource::collection($benefits);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Get Promotions
     */
    public function promotions()
    {
        $promotions = Promotion::orderBy('id', 'desc')->get();
        $data = PromotionResource::collection($promotions);

        return response()->json(['data' => $data], 200);
    }

    /*
     * Apply Voucher
     */
    public function applyVoucher(Request $request)
    {
        $voucher = Voucher::where('code', $request->code)->first();

        // 1. Cek voucher ada
        if (!$voucher) {
            return response()->json([
                'message' => 'Voucher not found',
            ], 404);
        }

        // 2. Cek status & tanggal berlaku
        if ($voucher->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Voucher is not active',
            ], 400);
        }

        // 3. Cek target user
        if ($voucher->target !== 'all') {

            // voucher users
            $voucherUsers = $voucher->users()->where('user_id', $request->user_id)->first();

            if (!$voucherUsers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher is not for you',
                ], 400);
            }
        }

        // 4. Cek minimum belanja (kalau ada di request total belanja)
        if ($voucher->min_transaction_value && $request->total < $voucher->min_transaction_value) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum order amount is ' . $voucher->min_transaction_value,
            ], 400);
        }

        // 5. Cek kuota
        $limit = VoucherUsage::where('voucher_id', $voucher->id)->where('user_id', $request->user_id)->count();
        if ($limit >= $voucher->limit) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher usage limit reached',
            ], 400);
        }


        // 6. Voucher untuk product
        if ($voucher->type === 'product') {
            $found = false;

            foreach ($request->product as $item) {
                $voucherProduct = VoucherProduct::where('variantsize_id', $item['id'])
                    ->where('voucher_id', $voucher->id)
                    ->first();
                if ($voucherProduct) {
                    $found = true;
                    break; // cukup 1 product cocok
                }
            }

            if (!$found) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found in voucher',
                ], 400);
            }
        }


        // Jika semua valid
        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully',
            'data'    => [
                'voucher_type'  => $voucher->type,
                'discount_type'  => $voucher->amount_type,
                'discount_value' => $voucher->amount,
                'max_discount'   => $voucher->max_value,
                'min_transaction' => $voucher->min_transaction_value
            ],
        ], 200);
    }

    public function abouts()
    {
        $about = About::with([
            'partnerSection',
            'whyUsFeatures',
            'profileSection'
        ])->first();

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'Data About belum tersedia.'
            ], 404);
        }

        return response()->json($about, 200);
    }

    // get voucher users
    public function myVouchers()
    {
        $user = Auth::user();

        // pastikan user login
        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        // ambil semua voucher yang dimiliki user + relasi voucher dan user
        $vouchers = VoucherUser::with(['voucher', 'user'])
            ->where('user_id', $user->id)
            ->get();

        return response()->json($vouchers, 200);
    }
}
