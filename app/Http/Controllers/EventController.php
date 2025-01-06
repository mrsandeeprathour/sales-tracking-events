<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\EventSale;

use App\Jobs\ProcessEventSales;
class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the current page from the request (default to 1 if not present)
        $currentPage = $request->get('page', 1);

        // Fetch the events with pagination (10 records per page)
        $events = Event::orderBy('created_at', 'desc') // Or replace 'created_at' with any field you need
        ->paginate(10);
        // Return the events data with pagination details to the frontend
        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate the request data


    try {
        $validated = $request->validate([
            'event_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:active,inactive',
            'shop' => 'required', // Assuming 'shop' is either the shop name or shop identifier
        ]);

        // Find the user by the 'shop' field
        $user = User::where('name', $request->shop)->first();

        // Check if the user exists
        if (!$user) {
            return Inertia::render('Events/Index', [
                'errors' => ['shop' => 'User not found for the provided shop.'],
             ]);
        }

        // Save the event with the user's id in the shop_id field
        $eventData = $validated;
        $eventData['shop_id'] = $user->id; // Save the user's id in the 'shop_id' field

        // Create the event
        $event = Event::create($eventData);

        // Dispatch the event processing job
        ProcessEventSales::dispatch($event, $user);

        // Fetch all events for the shop to display


    } catch (\Illuminate\Validation\ValidationException $e) {

             return Inertia::render('Events/Index', [
            'errors' => $e->errors(),
             ]);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the event by ID with related event sales, products, variants, and images
        $event = Event::with([
            'eventSales.product.variants.images', // Eager load product variants and images
        ])->find($id);

        // If the event is not found, return a 404 response
        if (!$event) {
            return Inertia::render('Events/Show', [
                'errors' => ['message' => 'Event not found'],
            ]);
        }

        // Safely check if event_sales is not null
        $eventSales = $event->eventSales ?? collect();  // Use empty collection if null

        // If there are no event_sales, return an empty collection for event_sales
        if ($eventSales->isEmpty()) {
            return Inertia::render('Events/Show', [
                'event' => $event,
                'event_sales' => [],  // Return empty sales data
            ]);
        }

        // Group event sales by product_id
        $groupedSales = $eventSales->groupBy('product_id');

        // Map each group to include the necessary product details with variants and images
        $groupedSales = $groupedSales->map(function ($sales) use ($event) {
            // Assuming 'product' is an eager-loaded relation
            $product = $sales->first()->product;

            // Map variants and include their inventory and images
            $variants = $product->variants->map(function ($variant) use ($sales,$event) {
                // Filter sales for this specific variant
                $filteredSales = $sales->filter(function ($sale) use ($variant,$event) {
                    return $sale->variant_id === $variant->id; // Match variant_id with variant id
                });

                if ($filteredSales->isEmpty()) {
                    return null; // Skip if no sales match the filter
                }

                // Aggregate inventory values from the filtered sales
                return [
                    'id' => $variant->id,
                    'variant_id' => $variant->variant_id,
                    'title' => $variant->title,
                    'inhand_inventory' => $filteredSales->sum('inhand_inventory'),
                    'sold_inventory' => $filteredSales->sum('sold_inventory'),
                    'total_inventory' => $filteredSales->sum('total_inventory'),
                    'images' => $variant->images->map(function ($image) {
                        // Return image details
                        return [
                            'src' => $image->src, // Assuming image has 'src'
                            'alt' => $image->alt_text, // Assuming image has 'alt_text'
                        ];
                    })->toArray(),
                ];
            })->filter(); // Remove null variants

            return [
                'id' => $product->id,
                'event_id'=>$event->id,
                'title' => $product->title,
                'handle' => $product->handle,
                'status' => $product->status,
                'variants' => $variants,
            ];
        });
        // Pass the event data and grouped sales to the Inertia component
        return Inertia::render('Events/Show', [
            'event' => $event,
            'event_sales' => $groupedSales,  // Pass the grouped sales data with product details
        ]);
    }





    public function fetchEventSaleDetails(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'event_id' => 'required|integer',
            'product_id' => 'required|integer',
        ]);

        // Fetch event sale data based on event_id and product_id
        $eventSales = EventSale::with(['product.variants.images']) // Eager load product, variants, and images
            ->where('event_id', $validated['event_id'])
            ->where('product_id', $validated['product_id'])
            ->get();

        // If no event sales found, return a 404 response
        if ($eventSales->isEmpty()) {
            return response()->json(['message' => 'No event sale found'], 404);
        }
        $eventSales = $eventSales->groupBy('product_id');

        // Map through the grouped event sales and format the data
        $groupedByProduct = $eventSales->map(function ($groupedEventSales) {
            // Access the product for the grouped event sales
            $product = $groupedEventSales->first()->product;

            // Process each variant and associated images for the product
            $variants = $product->variants->map(function ($variant) use ($groupedEventSales) {
                // Filter the event sales by variant_id
                $filteredEventSales = $groupedEventSales->filter(function ($eventSale) use ($variant) {
                    return $eventSale->variant_id == $variant->id;
                });

                return [
                    'id' => $variant->id,
                    'variant_id' => $variant->variant_id ?? null, // Handle optional fields
                    'title' => $variant->title,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    // Sum the inventory across filtered event sales for the specific variant
                    'inhand_inventory' => $filteredEventSales->sum('inhand_inventory'),
                    'sold_inventory' => $filteredEventSales->sum('sold_inventory'),
                    'total_inventory' => $filteredEventSales->sum('total_inventory'),
                    'images' => $variant->images->map(function ($image) {
                        return [
                            'src' => $image->src ?? null,
                            'alt' => $image->alt_text ?? null,
                        ];
                    })->toArray(),
                ];
            });

            // Return the formatted product data
            return [
                'title' => $product->title,
                'handle' => $product->handle,
                'variants' => $variants,
            ];
        });

        // Return the event sale data to the Inertia EventSaleDetails page
        return Inertia::render('Events/EventSaleDetails', [
            'eventSale' => $groupedByProduct,
        ]);
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
