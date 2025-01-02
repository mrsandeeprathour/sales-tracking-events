<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Image;
use App\Models\User;
use App\Traits\ShopifyInventoryTrait;
use App\Traits\ShopifyProductTrait;


use Http;
class ProductController extends Controller
{
    use ShopifyInventoryTrait,ShopifyProductTrait;
    public function index(Request $request)
    {

        $shop = User::where('name', $request->query('shop'))->first();

        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }
        // return $this->getVariantInventory("45776599187622",$shop->name,$shop->password);
        $products = Product::where('shop_id', $shop->id)->with(['variants.images'])->get(); // Get all products
        return response()->json($products);
    }

    // Show a specific product with variants and images
    public function show($id)
    {
        $product = Product::with(['variants', 'images'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }


    public function syncProducts(Request $request)
    {
        $shop = User::where('name', $request->query('shop'))->first();

        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], 404);
        }

        // Sync products from Shopify using the trait
        return $this->syncShopifyProducts($shop->id,$shop->name, $shop->password);

        // set_time_limit(0); // Remove time limit

        // $shop = User::where('name', $request->query('shop'))->first();

        // if (!$shop) {
        //     return response()->json(['message' => 'Shop not found'], 404);
        // }

        // // Shopify API endpoint for products
        // $perPage = 100;  // Shopify's limit per page is 100
        // $url = 'https://' . $shop->name . '/admin/api/' . env('SHOPIFY_API_VERSION') . '/products.json';
        // $totalFetched = 0; // Track the number of products fetched
        // $hasNextPage = true; // Flag to check if there are more pages
        // $pageInfo = null; // To store the page_info for pagination

        // // Loop through pages until all products are fetched
        // while ($hasNextPage) {
        //     // Get products from Shopify API with pagination using the Http facade
        //     $response = Http::withHeaders([
        //         'X-Shopify-Access-Token' => $shop->password,
        //     ])->get($url, [
        //         'limit' => $perPage,
        //         'page_info' => $pageInfo, // Include page_info for pagination
        //     ]);

        //     // Check if the response contains products
        //     if ($response->successful() && isset($response->json()['products'])) {
        //         $productsData = $response->json()['products']; // Access the products directly from the response

        //         // Loop through all products and save to the database
        //         foreach ($productsData as $productData) {
        //             // Create or update the product
        //             $product = Product::updateOrCreate(
        //                 ['product_id' => $productData['id'], 'shop_id' => $shop->id],
        //                 [
        //                     'title' => $productData['title'],
        //                     'vendor' => $productData['vendor'],
        //                     'handle' => $productData['handle'],
        //                     'status' => $productData['status'],
        //                     "tags"=>$productData['tags'],
        //                 ]
        //             );

        //             // Save product variants
        //             foreach ($productData['variants'] as $variantData) {
        //                 $variant = Variant::updateOrCreate(
        //                     ['variant_id' => $variantData['id'], 'product_id' => $product->id],
        //                     [
        //                         'title' => $variantData['title'],
        //                         'price' => $variantData['price'],
        //                         'sku' => $variantData['sku'],
        //                         'weight'=>$variantData['weight']
        //                     ]
        //                 );

        //                 // Save product images
        //                 foreach ($productData['images'] as $imageData) {
        //                     Image::updateOrCreate(
        //                         [
        //                             'image_id' => $imageData['id'],    // Check if image_id exists
        //                             'product_id' => $product->id,      // Ensure image is linked to the correct product
        //                             'variant_id' => $variant->id       // Ensure image is linked to the correct variant
        //                         ],
        //                         [
        //                             'src' => $imageData['src']          // Update the source URL of the image
        //                         ]
        //                     );
        //                 }
        //             }
        //         }

        //         // Increment total products fetched
        //         $totalFetched += count($productsData);

        //         // Check if there's another page of products
        //         $nextPageLink = $response->header('Link');
        //         if ($nextPageLink && strpos($nextPageLink, 'rel="next"') !== false) {
        //             // Extract the next page URL from the 'next' link in the header
        //             preg_match('/<([^>]+)>; rel="next"/', $nextPageLink, $matches);
        //             $url = $matches[1];  // Update the URL for the next page

        //             // Extract the page_info parameter from the next page URL for pagination
        //             $urlComponents = parse_url($url);
        //             parse_str($urlComponents['query'], $queryParams);
        //             $pageInfo = $queryParams['page_info'];  // Set the page_info for the next request
        //         } else {
        //             $hasNextPage = false;  // No next page, stop the loop
        //         }
        //     } else {
        //         // In case of error or no products in the response
        //         return response()->json(['message' => 'Failed to fetch products from Shopify.', 'error' => $response->json()], 500);
        //     }
        // }

        // return response()->json(['message' => "$totalFetched products synced successfully!"], 200);
    }
}
