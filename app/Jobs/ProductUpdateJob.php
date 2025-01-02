<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use stdClass;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Image;
use App\Models\User;
use Log;

class ProductUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Shop's myshopify domain
     *
     * @var ShopDomain|string
     */
    public $shopDomain;

    /**
     * The webhook data
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
        // Convert domain
        $this->shopDomain = ShopDomain::fromNative($this->shopDomain);
        $shop = User::where('name', $this->shopDomain->toNative())->first();
        $productData = $this->data;
        Log::info(['$productData'=>$productData]);
       if(isset($productData)) {
            $product = Product::updateOrCreate(
                ['product_id' => $productData->id,'shop_id' => $shop->id],
                [
                    'title' => $productData->title,
                    'vendor' => $productData->vendor,
                    'handle' => $productData->handle,
                    'status' => $productData->status,
                    'tags' =>  !empty($productData->tags) && is_array($productData->tags)
                                ? implode(',', array_filter($productData->tags))
                                : (is_string($productData->tags) ? $productData->tags : ''),
                ]
            );
            if(isset($productData->variants)) {
                foreach ($productData->variants as $variantData) {
                    $variant = Variant::updateOrCreate(
                        ['variant_id' => $variantData->id, 'product_id' => $product->id],
                        [
                            'title' => $variantData->title,
                            'price' => $variantData->price,
                            'sku' => $variantData->sku,
                            'weight' => $variantData->weight,

                        ]
                    );

                    // Save product images
                    if(isset($productData->images)) {
                        foreach ($productData->images as $imageData) {
                            Image::updateOrCreate(
                                [
                                    'image_id' => $imageData->id,    // Check if image_id exists
                                    'product_id' => $product->id,      // Ensure image is linked to the correct product
                                    'variant_id' => $variant->id       // Ensure image is linked to the correct variant
                                ],
                                [
                                    'src' => $imageData->src          // Update the source URL of the image
                                ]
                            );
                        }
                    }


                }
            }
       }

        // Do what you wish with the data
        // Access domain name as $this->shopDomain->toNative()
    }
}
