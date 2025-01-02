<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait ShopifyInventoryTrait
{
    /**
     * Get the total inventory of a variant.
     *
     * @param string $variantId The Shopify variant ID.
     * @param string $shopDomain The Shopify store domain.
     * @param string $accessToken The Shopify store access token.
     * @return int|null Total inventory or null on failure.
     */
    public function getVariantInventory(string $variantId, string $shopDomain, string $accessToken): ?int
    {
        $query = <<<GRAPHQL
        {
            productVariant(id: "gid://shopify/ProductVariant/$variantId") {
                inventoryQuantity
            }
        }
        GRAPHQL;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Shopify-Access-Token' => $accessToken,
            ])->post("https://$shopDomain/admin/api/2023-01/graphql.json", [
                'query' => $query,
            ]);

            $data = $response->json();

            if (isset($data['data']['productVariant']['inventoryQuantity'])) {
                return $data['data']['productVariant']['inventoryQuantity'];
            }

            return null;
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error fetching variant inventory: ' . $e->getMessage());
            return null;
        }
    }
}
