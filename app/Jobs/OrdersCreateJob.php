<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use App\Models\EventSales;
use App\Models\Variant;
use App\Models\User; // Assuming User model is where you store shop users
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;

class OrdersCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain.
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data.
     *
     * @var object
     */
    public $data;

    /**
     * Create a new job instance.
     *
     * @param string   $shopDomain The shop's myshopify domain.
     * @param stdClass $data       The webhook data (JSON decoded).
     *
     * @return void
     */
    public function __construct($shopDomain, $data)
    {
        $this->shopDomain = $shopDomain;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Convert domain
            $this->shopDomain = ShopDomain::fromNative($this->shopDomain);

            // Log the job execution start
            Log::info("Processing order webhook for shop: {$this->shopDomain->toNative()}");

            // Get user based on shop domain (assuming shop domain matches user 'name')
            $user = User::where('name', $this->shopDomain->toNative())->first();

            if (!$user) {
                Log::warning("User not found for shop domain: {$this->shopDomain->toNative()}");
                return;
            }

            // Get shop_id from user model (assuming 'shop_id' exists in user table)
            $shopId = $user->shop_id;

            // Retrieve active events for this shop
            $activeEvents = Event::where('status', 'active')->where('shop_id', $shopId)->get();

            if ($activeEvents->isEmpty()) {
                Log::warning("No active events found for shop: {$this->shopDomain->toNative()}");
                return;
            }

            // Retrieve event IDs
            $eventIds = $activeEvents->pluck('id');

            // Retrieve event sales for active events
            $eventSales = EventSales::whereIn('event_id', $eventIds)->get();

            if ($eventSales->isEmpty()) {
                Log::warning("No event sales found for active events in shop: {$this->shopDomain->toNative()}");
                return;
            }

            // Process each line item in the order
            foreach ($this->data->line_items as $lineItem) {
                // Get the variant using the variant_id from the line item
                $variant = Variant::where('variant_id', $lineItem['variant_id'])->first();

                if ($variant) {
                    // Match the variant_id with event_sales variant_id
                    $matchedSale = $eventSales->firstWhere('variant_id', $variant->variant_id);

                    if ($matchedSale) {
                        // Update sold inventory
                        $matchedSale->sold_inventory += $lineItem['quantity'];

                        // Recalculate in-hand inventory
                        $matchedSale->inhand_inventory = $matchedSale->total_inventory - $matchedSale->sold_inventory;

                        // Save the changes
                        $matchedSale->save();

                        Log::info("Updated inventory for variant_id: {$variant->variant_id} in shop: {$this->shopDomain->toNative()}");
                    } else {
                        Log::warning("No matching event sale found for variant_id: {$variant->variant_id} in shop: {$this->shopDomain->toNative()}");
                    }
                } else {
                    Log::warning("Variant not found for variant_id: {$lineItem['variant_id']} in shop: {$this->shopDomain->toNative()}");
                }
            }

            // Log the job execution success
            Log::info("Order processing completed successfully for shop: {$this->shopDomain->toNative()}");
        } catch (\Exception $e) {
            // Log the error
            Log::error("Error processing order webhook for shop: {$this->shopDomain->toNative()} - {$e->getMessage()}");
        }
    }
}
