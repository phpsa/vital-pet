<?php

namespace Tests\Unit\Services;

use App\Models\ProductVariantRegionalStock;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Country;
use Lunar\Models\Language;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

/**
 * @group services.inventory
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['default' => true]);
        $this->service = new InventoryService;
    }

    // -------------------------------------------------------------------------
    // availableQuantityForPurchasable
    // -------------------------------------------------------------------------

    public function test_always_purchasable_returns_max_int_regardless_of_country(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'always',
            'stock'                  => 5,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $this->assertSame(PHP_INT_MAX, $this->service->availableQuantityForPurchasable($variant));
        $this->assertSame(PHP_INT_MAX, $this->service->availableQuantityForPurchasable($variant, 999));
    }

    public function test_in_stock_variant_without_regional_stock_uses_global(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $this->assertSame(10, $this->service->availableQuantityForPurchasable($variant));
    }

    public function test_in_stock_or_on_backorder_without_regional_stock_adds_global_totals(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock_or_on_backorder',
            'stock'                  => 8,
            'backorder'              => 4,
            'regional_stock_enabled' => false,
        ]);

        $this->assertSame(12, $this->service->availableQuantityForPurchasable($variant));
    }

    public function test_unknown_purchasable_value_returns_zero(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'not_purchasable',
            'stock'                  => 99,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $this->assertSame(0, $this->service->availableQuantityForPurchasable($variant));
    }

    // -------------------------------------------------------------------------
    // Regional stock enabled — matching country
    // -------------------------------------------------------------------------

    public function test_regional_stock_enabled_matching_country_adds_regional_to_global_for_in_stock(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 5,
            'backorder'          => 0,
        ]);

        $this->assertSame(15, $this->service->availableQuantityForPurchasable($variant, $country->id));
    }

    public function test_regional_stock_enabled_matching_country_adds_regional_stock_and_backorder_to_global(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock_or_on_backorder',
            'stock'                  => 10,
            'backorder'              => 5,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 3,
            'backorder'          => 2,
        ]);

        // global(10+5) + regional(3+2) = 20
        $this->assertSame(20, $this->service->availableQuantityForPurchasable($variant, $country->id));
    }

    // -------------------------------------------------------------------------
    // Regional stock enabled — non-matching country falls back to global only
    // -------------------------------------------------------------------------

    public function test_regional_stock_enabled_non_matching_country_uses_global_only(): void
    {
        $countryA = Country::factory()->create();
        $countryB = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => true,
        ]);

        // Regional row only for country A
        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $countryA->id,
            'stock'              => 99,
            'backorder'          => 0,
        ]);

        // Country B has no regional row → global only
        $this->assertSame(10, $this->service->availableQuantityForPurchasable($variant, $countryB->id));
    }

    // -------------------------------------------------------------------------
    // No country known (pre-checkout) → global only
    // -------------------------------------------------------------------------

    public function test_null_country_uses_global_stock_only_even_when_regional_enabled(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 100,
            'backorder'          => 0,
        ]);

        $this->assertSame(10, $this->service->availableQuantityForPurchasable($variant, null));
    }

    // -------------------------------------------------------------------------
    // regional_stock_enabled = false → regional rows are ignored
    // -------------------------------------------------------------------------

    public function test_regional_rows_ignored_when_regional_stock_disabled(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 50,
            'backorder'          => 0,
        ]);

        // regional rows should be ignored even with matching country
        $this->assertSame(10, $this->service->availableQuantityForPurchasable($variant, $country->id));
    }

    public function test_in_stock_quantity_uses_global_and_matching_regional_stock_only(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock_or_on_backorder',
            'stock'                  => 4,
            'backorder'              => 20,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 6,
            'backorder'          => 99,
        ]);

        // Only in-stock quantities should be counted (global + regional stock).
        $this->assertSame(10, $this->service->inStockQuantityForPurchasable($variant, $country->id));
    }

    public function test_in_stock_quantity_returns_global_only_when_country_missing(): void
    {
        $country = Country::factory()->create();

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock_or_on_backorder',
            'stock'                  => 5,
            'backorder'              => 10,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $country->id,
            'stock'              => 8,
            'backorder'          => 0,
        ]);

        $this->assertSame(5, $this->service->inStockQuantityForPurchasable($variant, null));
    }

    // -------------------------------------------------------------------------
    // validateRequestedQuantity
    // -------------------------------------------------------------------------

    public function test_validate_requested_quantity_ok_when_within_available(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $result = $this->service->validateRequestedQuantity($variant, 10);

        $this->assertTrue($result['ok']);
        $this->assertSame(10, $result['available']);
    }

    public function test_validate_requested_quantity_fails_when_exceeds_available(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 3,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $result = $this->service->validateRequestedQuantity($variant, 5);

        $this->assertFalse($result['ok']);
        $this->assertSame(3, $result['available']);
    }

    public function test_validate_requested_quantity_zero_available_returns_zero(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 0,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $result = $this->service->validateRequestedQuantity($variant, 1);

        $this->assertFalse($result['ok']);
        $this->assertSame(0, $result['available']);
    }

    // -------------------------------------------------------------------------
    // stockStatusForPurchasable
    // -------------------------------------------------------------------------

    public function test_stock_status_always_purchasable_shows_in_stock_success(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'always',
            'regional_stock_enabled' => false,
        ]);

        $status = $this->service->stockStatusForPurchasable($variant);

        $this->assertSame('5+ in Stock', $status['text']);
        $this->assertSame('success', $status['tone']);
    }

    public function test_stock_status_out_of_stock_shows_danger(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 0,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $status = $this->service->stockStatusForPurchasable($variant);

        $this->assertSame('Out of Stock', $status['text']);
        $this->assertSame('danger', $status['tone']);
    }

    public function test_stock_status_low_stock_shows_exact_count(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 3,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $status = $this->service->stockStatusForPurchasable($variant);

        $this->assertSame('3 in Stock', $status['text']);
        $this->assertSame('success', $status['tone']);
    }

    public function test_stock_status_high_stock_shows_five_plus(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 99,
            'backorder'              => 0,
            'regional_stock_enabled' => false,
        ]);

        $status = $this->service->stockStatusForPurchasable($variant);

        $this->assertSame('5+ in Stock', $status['text']);
    }

    public function test_stock_status_on_backorder_shows_warning_tone(): void
    {
        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock_or_on_backorder',
            'stock'                  => 0,
            'backorder'              => 5,
            'regional_stock_enabled' => false,
        ]);

        $status = $this->service->stockStatusForPurchasable($variant);

        $this->assertSame('warning', $status['tone']);
    }

    // -------------------------------------------------------------------------
    // requestedByPurchasable
    // -------------------------------------------------------------------------

    public function test_requested_by_purchasable_aggregates_quantities(): void
    {
        $lines = [
            ['purchasable_type' => 'Lunar\\Models\\ProductVariant', 'purchasable_id' => 1, 'quantity' => 2],
            ['purchasable_type' => 'Lunar\\Models\\ProductVariant', 'purchasable_id' => 1, 'quantity' => 3],
            ['purchasable_type' => 'Lunar\\Models\\ProductVariant', 'purchasable_id' => 2, 'quantity' => 5],
        ];

        $result = $this->service->requestedByPurchasable($lines);

        $this->assertSame(5, $result['Lunar\\Models\\ProductVariant:1']);
        $this->assertSame(5, $result['Lunar\\Models\\ProductVariant:2']);
    }

    public function test_requested_by_purchasable_skips_invalid_lines(): void
    {
        $lines = [
            ['purchasable_type' => '', 'purchasable_id' => 0, 'quantity' => 99],
            ['purchasable_type' => 'Lunar\\Models\\ProductVariant', 'purchasable_id' => 1, 'quantity' => 4],
        ];

        $result = $this->service->requestedByPurchasable($lines);

        $this->assertCount(1, $result);
        $this->assertSame(4, $result['Lunar\\Models\\ProductVariant:1']);
    }

    // -------------------------------------------------------------------------
    // Future country data-driven support
    // -------------------------------------------------------------------------

    public function test_future_country_added_as_row_is_supported_without_code_changes(): void
    {
        $countryA = Country::factory()->create();
        $countryB = Country::factory()->create(); // new country added later

        $variant = ProductVariant::factory()->create([
            'purchasable'            => 'in_stock',
            'stock'                  => 10,
            'backorder'              => 0,
            'regional_stock_enabled' => true,
        ]);

        ProductVariantRegionalStock::create([
            'product_variant_id' => $variant->id,
            'country_id'         => $countryB->id,
            'stock'              => 7,
            'backorder'          => 0,
        ]);

        // Country A has no row → global only
        $this->assertSame(10, $this->service->availableQuantityForPurchasable($variant, $countryA->id));

        // Country B has a row → global + regional
        $this->assertSame(17, $this->service->availableQuantityForPurchasable($variant, $countryB->id));
    }
}
