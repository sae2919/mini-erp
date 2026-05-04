<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;
    private Product $product;
    private Supplier $supplier;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InventoryService::class);

        $category = Category::factory()->create();

        $this->product = Product::factory()->create([
            'category_id'      => $category->id,
            'stock_quantity'   => 10,
            'cost_price'       => 50.00,
            'price'            => 100.00,
            'low_stock_threshold' => 5,
        ]);

        $this->supplier = Supplier::factory()->create();

        // ActivityLogger calls auth()->id() — provide an authenticated user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 1 — Purchase increases stock
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function purchase_increases_stock_quantity(): void
    {
        $stockBefore = $this->product->stock_quantity; // 10

        $this->service->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'notes'         => null,
            'items'         => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 20,
                    'cost_price' => 50.00,
                ],
            ],
        ]);

        $this->product->refresh();

        $this->assertEquals(
            $stockBefore + 20,
            $this->product->stock_quantity,
            'Stock should increase by the purchased quantity.'
        );
    }

    /** @test */
    public function purchase_creates_purchase_and_items_in_database(): void
    {
        $purchase = $this->service->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'notes'         => 'Test PO',
            'items'         => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 5,
                    'cost_price' => 50.00,
                ],
            ],
        ]);

        $this->assertDatabaseHas('purchases', [
            'id'          => $purchase->id,
            'supplier_id' => $this->supplier->id,
            'total_amount'=> 250.00, // 5 × 50
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id'  => $this->product->id,
            'quantity'    => 5,
            'cost_price'  => 50.00,
        ]);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 2 — Sale decreases stock
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function sale_decreases_stock_quantity(): void
    {
        $stockBefore = $this->product->stock_quantity; // 10

        $this->service->createSale([
            'sale_date'     => now()->toDateString(),
            'customer_name' => 'Test Customer',
            'notes'         => null,
            'items'         => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 3,
                    'selling_price' => 100.00,
                ],
            ],
        ]);

        $this->product->refresh();

        $this->assertEquals(
            $stockBefore - 3,
            $this->product->stock_quantity,
            'Stock should decrease by the sold quantity.'
        );
    }

    /** @test */
    public function sale_snapshots_cost_price_at_time_of_sale(): void
    {
        $costPriceAtSale = $this->product->cost_price; // 50.00

        $sale = $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 1,
                    'selling_price' => 100.00,
                ],
            ],
        ]);

        // Now change the product cost price
        $this->product->update(['cost_price' => 999.00]);

        // The sale item should still have the original cost price
        $this->assertDatabaseHas('sale_items', [
            'sale_id'    => $sale->id,
            'product_id' => $this->product->id,
            'cost_price' => $costPriceAtSale,
        ]);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 3 — Sale blocked when stock insufficient
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function sale_throws_exception_when_stock_is_insufficient(): void
    {
        $this->expectException(ValidationException::class);

        // Product has 10 in stock — try to sell 99
        $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 99,
                    'selling_price' => 100.00,
                ],
            ],
        ]);
    }

    /** @test */
    public function failed_sale_does_not_modify_stock(): void
    {
        $stockBefore = $this->product->stock_quantity; // 10

        try {
            $this->service->createSale([
                'sale_date' => now()->toDateString(),
                'items'     => [
                    [
                        'product_id'    => $this->product->id,
                        'quantity'      => 99,
                        'selling_price' => 100.00,
                    ],
                ],
            ]);
        } catch (ValidationException) {
            // Expected — swallow it
        }

        $this->product->refresh();

        $this->assertEquals(
            $stockBefore,
            $this->product->stock_quantity,
            'Stock must not change when a sale fails validation.'
        );

        // Also confirm no Sale record was created
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
    }

    /** @test */
    public function sale_blocked_when_requesting_more_than_available_across_duplicate_items(): void
    {
        // Product has 10. Request 6 + 6 = 12 in two separate line items.
        // validateStockAvailability() aggregates these before any write.
        $this->expectException(ValidationException::class);

        $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 6,
                    'selling_price' => 100.00,
                ],
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 6,
                    'selling_price' => 100.00,
                ],
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 4 — Delete purchase reverses stock
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function deleting_purchase_decrements_stock_back(): void
    {
        $purchase = $this->service->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items'         => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 20,
                    'cost_price' => 50.00,
                ],
            ],
        ]);

        $stockAfterPurchase = $this->product->fresh()->stock_quantity; // 30

        $this->service->deletePurchase($purchase);

        $this->product->refresh();

        $this->assertEquals(
            $stockAfterPurchase - 20,
            $this->product->stock_quantity,
            'Deleting a purchase should reverse the stock increment.'
        );

        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 5 — Delete purchase blocked when stock already consumed
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function deleting_purchase_blocked_when_stock_already_consumed_by_sales(): void
    {
        // Start: product has 10 in stock
        // Purchase 5 more → stock = 15
        $purchase = $this->service->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items'         => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 5,
                    'cost_price' => 50.00,
                ],
            ],
        ]);

        // Sell 13 → stock = 2
        $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 13,
                    'selling_price' => 100.00,
                ],
            ],
        ]);

        // Now try to reverse the purchase (would subtract 5, leaving stock = -3)
        // This must be blocked
        $this->expectException(ValidationException::class);

        $this->service->deletePurchase($purchase->fresh());
    }

    /** @test */
    public function blocked_purchase_deletion_does_not_modify_stock(): void
    {
        $purchase = $this->service->createPurchase([
            'supplier_id'   => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items'         => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 5,
                    'cost_price' => 50.00,
                ],
            ],
        ]);

        // Sell everything
        $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 13,
                    'selling_price' => 100.00,
                ],
            ],
        ]);

        $stockBeforeAttempt = $this->product->fresh()->stock_quantity; // 2

        try {
            $this->service->deletePurchase($purchase->fresh());
        } catch (ValidationException) {
            // Expected
        }

        $this->assertEquals(
            $stockBeforeAttempt,
            $this->product->fresh()->stock_quantity,
            'Stock must not change when purchase deletion is blocked.'
        );

        // Purchase record must still exist
        $this->assertNotSoftDeleted('purchases', ['id' => $purchase->id]);
    }

    // ════════════════════════════════════════════════════════════
    //  TEST 6 — Delete sale restores stock
    // ════════════════════════════════════════════════════════════

    /** @test */
    public function deleting_sale_restores_stock(): void
    {
        $sale = $this->service->createSale([
            'sale_date' => now()->toDateString(),
            'items'     => [
                [
                    'product_id'    => $this->product->id,
                    'quantity'      => 4,
                    'selling_price' => 100.00,
                ],
            ],
        ]);

        $stockAfterSale = $this->product->fresh()->stock_quantity; // 6

        $this->service->deleteSale($sale);

        $this->assertEquals(
            $stockAfterSale + 4,
            $this->product->fresh()->stock_quantity,
            'Cancelling a sale should restore the decremented stock.'
        );

        $this->assertSoftDeleted('sales', ['id' => $sale->id]);
    }
}