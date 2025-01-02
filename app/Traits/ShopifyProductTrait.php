<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Image;

trait ShopifyProductTrait
{
    /**
     * Sync products from Shopify using GraphQL
     *
     * @param  string  $shopName
     * @param  string  $shopPassword
     * @return \Illuminate\Http\Response
     */
    public function syncShopifyProducts($shopId, $shopName, $shopPassword)
    {
        set_time_limit(0); // Remove time limit for the process

        // Shopify GraphQL endpoint
        $url = "https://{$shopName}/admin/api/2023-10/graphql.json";
        $totalFetched = 0;

        // GraphQL query for fetching products
        $query = '
            query($cursor: String) {
                products(first: 100, after: $cursor) {
                    edges {
                        node {
                            id
                            title
                            vendor
                            handle
                            status
                            tags
                            variants(first: 100) {
                                edges {
                                    node {
                                        id
                                        title
                                        price
                                        sku
                                        weight
                                    }
                                }
                            }
                            images(first: 100) {
                                edges {
                                    node {
                                        id
                                        src
                                        altText
                                    }
                                }
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
        ';

        // Loop through pages until all products are fetched
        $hasNextPage = true;
        $cursor = null;

        while ($hasNextPage) {
            // Perform the GraphQL request
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $shopPassword,
            ])->post($url, [
                'query' => $query,
                'variables' => [
                    'cursor' => $cursor,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $products = $data['data']['products']['edges'];

                // Loop through all products and save to the database
                foreach ($products as $productEdge) {
                    $productData = $productEdge['node'];

                    // Create or update the product
                    $product = Product::updateOrCreate(
                        [
                            'product_id' => $this->extractShopifyId($productData['id']),
                            'shop_id' => $shopId,
                        ],
                        [
                            'title' => $productData['title'],
                            'vendor' => $productData['vendor'],
                            'handle' => $productData['handle'],
                            'status' => $productData['status'],
                            'tags' =>  !empty($productData['tags']) && is_array($productData['tags'])
                                        ? implode(',', array_filter($productData['tags']))
                                        : (is_string($productData['tags']) ? $productData['tags'] : ''),
                        ]
                    );

                    // Save product variants and images
                    foreach ($productData['variants']['edges'] as $variantEdge) {
                        $variantData = $variantEdge['node'];

                        $variant = Variant::updateOrCreate(
                            [
                                'variant_id' => $this->extractShopifyId($variantData['id']),
                                'product_id' => $product->id,
                            ],
                            [
                                'title' => $variantData['title'],
                                'price' => $variantData['price'],
                                'sku' => $variantData['sku'],
                                'weight' => $variantData['weight'],
                            ]
                        );

                        // Save product images and associate with the variant
                        foreach ($productData['images']['edges'] as $imageEdge) {
                            $imageData = $imageEdge['node'];

                            Image::updateOrCreate(
                                [
                                    'image_id' => $this->extractShopifyId($imageData['id']),
                                    'product_id' => $product->id,
                                    'variant_id' => $variant->id, // Associate with the current variant
                                ],
                                [
                                    'src' => $imageData['src'],
                                    'alt' => $imageData['altText'] ?? null,
                                ]
                            );
                        }
                    }
                }

                // Update the total number of products fetched
                $totalFetched += count($products);

                // Check if there is another page of products
                $pageInfo = $data['data']['products']['pageInfo'];
                $hasNextPage = $pageInfo['hasNextPage'];
                $cursor = $pageInfo['endCursor'];
            } else {
                return response()->json([
                    'message' => 'Failed to fetch products from Shopify.',
                    'error' => $response->json()
                ], 500);
            }
        }

        return response()->json(['message' => "$totalFetched products synced successfully!"], 200);
    }

    /**
     * Extract Shopify ID from the GraphQL global ID
     *
     * @param string $globalId
     * @return string
     */
    private function extractShopifyId($globalId)
    {
        $parts = explode('/', $globalId);
        return end($parts);
    }

}
