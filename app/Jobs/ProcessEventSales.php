<?php
namespace App\Jobs;

use App\Models\Event;
use App\Models\Product;
use App\Models\Variant;
use App\Models\EventSale;
use App\Traits\ShopifyInventoryTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessEventSales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ShopifyInventoryTrait;

    protected $event;
    protected $shop;

    /**
     * Create a new job instance.
     *
     * @param Event $event
     * @param $shop
     */
    public function __construct(Event $event, $shop)
    {
        $this->event = $event;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $shop = $this->shop;

        if (!$shop) {
            Log::error('Shop not found for event ID: ' . $this->event->id);
            return;
        }
        $products = Product::where('tags', 'REGEXP', '\\b' . preg_quote($this->event->event_name, '\\') . '\\b')
        ->where('shop_id', $shop->id)
        ->get();

            foreach ($products as $product) {
                $variants = Variant::where('product_id', $product->id)->get();

                foreach ($variants as $variant) {
                    $inventory = $this->getVariantInventory($variant->variant_id, $shop->name, $shop->password);

                if ($inventory === null) {
                    Log::error("Inventory fetch failed for variant ID: {$variant->variant_id}");
                    continue;
                }

                EventSale::updateOrCreate(
                    // The unique fields that determine if a record exists
                    [
                        'variant_id' => $variant->id,
                        'product_id' => $product->id,
                        'event_id' => $this->event->id,
                    ],
                    // The fields to update or set if creating a new record
                    [
                        'total_inventory' => $inventory,
                        'sold_inventory' => 0, // Assuming sold inventory starts at 0
                        'inhand_inventory' => $inventory,
                    ]
                );
            }
        }
    }
}
