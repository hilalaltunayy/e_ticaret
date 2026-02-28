<?php

use App\DTO\Shipping\ShippingSimulationRequestDTO;
use App\Repositories\ShippingAutomationRuleRepository;
use App\Services\ShippingSimulationService;
use CodeIgniter\Test\CIUnitTestCase;

class FakeShippingAutomationRuleRepository extends ShippingAutomationRuleRepository
{
    public function __construct(private array $rows)
    {
    }

    public function findActiveRules(): array
    {
        return $this->rows;
    }
}

final class ShippingSimulationServiceTest extends CIUnitTestCase
{
    public function testCodFiltering(): void
    {
        $service = new ShippingSimulationService(new FakeShippingAutomationRuleRepository([
            [
                'id' => 'r1',
                'rule_type' => 'cod',
                'supports_cod' => 1,
                'primary_company_id' => 'aras',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'rule_type' => 'city',
                'supports_cod' => 0,
                'primary_company_id' => 'yurtici',
                'is_active' => 1,
            ],
        ]), static fn (): array => []);

        $dto = ShippingSimulationRequestDTO::fromArray([
            'city' => 'Ankara',
            'sla_days' => 2,
            'desi' => 1.5,
            'cod' => true,
        ]);

        $result = $service->simulate($dto);

        $this->assertTrue($result['ok']);
        $this->assertSame('aras', $result['selected']['company_id']);
    }

    public function testCityMatchWithNullFallback(): void
    {
        $service = new ShippingSimulationService(new FakeShippingAutomationRuleRepository([
            [
                'id' => 'r1',
                'rule_type' => 'city',
                'city' => 'İstanbul',
                'city_slug' => 'istanbul',
                'supports_cod' => 0,
                'estimated_cost' => 15,
                'primary_company_id' => 'aras',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'rule_type' => 'city',
                'city' => null,
                'city_slug' => null,
                'supports_cod' => 0,
                'estimated_cost' => 9,
                'primary_company_id' => 'yurtici',
                'is_active' => 1,
            ],
        ]), static fn (): array => []);

        $dto = ShippingSimulationRequestDTO::fromArray([
            'city' => 'Ankara',
            'sla_days' => 3,
            'desi' => 1,
            'cod' => false,
        ]);

        $result = $service->simulate($dto);

        $this->assertSame('yurtici', $result['selected']['company_id']);
    }

    public function testDesiRangeControl(): void
    {
        $service = new ShippingSimulationService(new FakeShippingAutomationRuleRepository([
            [
                'id' => 'r1',
                'rule_type' => 'desi',
                'desi_min' => 0,
                'desi_max' => 3,
                'supports_cod' => 0,
                'estimated_cost' => 12,
                'primary_company_id' => 'aras',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'rule_type' => 'desi',
                'desi_min' => 4,
                'desi_max' => 10,
                'supports_cod' => 0,
                'estimated_cost' => 8,
                'primary_company_id' => 'yurtici',
                'is_active' => 1,
            ],
        ]), static fn (): array => []);

        $dto = ShippingSimulationRequestDTO::fromArray([
            'city' => 'İzmir',
            'sla_days' => 2,
            'desi' => 2,
            'cod' => false,
        ]);

        $result = $service->simulate($dto);

        $this->assertSame('aras', $result['selected']['company_id']);
    }

    public function testSlaCriteria(): void
    {
        $service = new ShippingSimulationService(new FakeShippingAutomationRuleRepository([
            [
                'id' => 'r1',
                'rule_type' => 'sla',
                'sla_max_days' => 1,
                'supports_cod' => 0,
                'estimated_cost' => 5,
                'primary_company_id' => 'aras',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'rule_type' => 'sla',
                'sla_max_days' => 3,
                'supports_cod' => 0,
                'estimated_cost' => 6,
                'primary_company_id' => 'yurtici',
                'is_active' => 1,
            ],
        ]), static fn (): array => []);

        $dto = ShippingSimulationRequestDTO::fromArray([
            'city' => 'Bursa',
            'sla_days' => 2,
            'desi' => 1,
            'cod' => false,
        ]);

        $result = $service->simulate($dto);

        $this->assertSame('yurtici', $result['selected']['company_id']);
    }

    public function testTieBreakerOrder(): void
    {
        $service = new ShippingSimulationService(new FakeShippingAutomationRuleRepository([
            [
                'id' => 'r1',
                'rule_type' => 'city',
                'city_slug' => 'ankara',
                'supports_cod' => 0,
                'estimated_cost' => 10,
                'sla_max_days' => 2,
                'priority' => 5,
                'primary_company_id' => 'aras',
                'is_active' => 1,
            ],
            [
                'id' => 'r2',
                'rule_type' => 'city',
                'city_slug' => 'ankara',
                'supports_cod' => 0,
                'estimated_cost' => 10,
                'sla_max_days' => 2,
                'priority' => 8,
                'primary_company_id' => 'yurtici',
                'is_active' => 1,
            ],
        ]), static fn (): array => []);

        $dto = ShippingSimulationRequestDTO::fromArray([
            'city' => 'Ankara',
            'sla_days' => 2,
            'desi' => 1,
            'cod' => false,
        ]);

        $result = $service->simulate($dto);

        $this->assertSame('yurtici', $result['selected']['company_id']);
    }
}
