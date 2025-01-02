<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;
use Illuminate\Support\Facades\Log;
use Http;

class User extends Authenticatable implements IShopModel
{
    use Notifiable;
    use ShopModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The "booted" method of the model. Here we can hook into the creation event.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->isDirty(['password'])) {
                 $user->createWebhooks();
            }
        });

        // Trigger updateWebhooks on user update
        static::updated(function ($user) {
            // Check if specific fields (like password or access token) have changed before updating webhooks
            if ($user->isDirty(['password'])) { // You can change this condition to check other fields like access_token if needed
                $user->createWebhooks();
            }
        });
    }

    /**
     * Function to create webhooks.
     */
    public function createWebhooks()
    {
        // This can be moved to a service class if needed
        $shop = $this->name; // Assuming you have the shopify domain stored
        $accessToken = $this->password; // Assuming you have access token

        // Call the function to create webhooks
        $this->registerWebhooks($shop, $accessToken);
    }

    /**
     * Register webhooks using Shopify GraphQL.
     */
    private function registerWebhooks($shop, $accessToken)
    {
        // Define the topics and webhook addresses
        $webhookTopics = [
            'APP_UNINSTALLED' => env('APP_URL').'/webhook/app-uninstalled',
            'ORDERS_CREATE' => env('APP_URL').'/webhook/orders-create',
            'ORDERS_UPDATED' => env('APP_URL').'/webhook/orders-update',
            'PRODUCTS_CREATE' => env('APP_URL').'/webhook/products-create',
            'PRODUCTS_UPDATE' => env('APP_URL').'/webhook/products-update',
        ];

        foreach ($webhookTopics as $topic => $address) {
            $this->registerWebhookGraphQL($shop, $accessToken, $topic, $address);
        }
    }

    /**
     * Register a webhook using GraphQL API.
     */
    private function registerWebhookGraphQL($shop, $accessToken, $topic, $address)
    {
        $url = "https://{$shop}/admin/api/2023-10/graphql.json";

        $query = '
            mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $address: URL!) {
                webhookSubscriptionCreate(input: {
                    topic: $topic
                    address: $address
                    format: JSON
                }) {
                    userErrors {
                        field
                        message
                    }
                    webhookSubscription {
                        id
                        topic
                        address
                    }
                }
            }
        ';

        $variables = [
            'topic' => $topic,
            'address' => $address,
        ];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $accessToken,
        ])->post($url, [
            'query' => $query,
            'variables' => $variables,
        ]);

        $data = $response->json();

        if ($response->successful() && empty($data['data']['webhookSubscriptionCreate']['userErrors'])) {
            Log::info("Webhook for topic {$topic} created successfully.");
        } else {
            Log::error("Failed to create webhook for topic {$topic}: " . json_encode($response));
        }
    }
}
