<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AssetModel;
use App\Models\LoanModel;
use App\Services\AssetService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class AssetServiceTest extends CIUnitTestCase
{
    private function mockAssetModel(?array $findReturn, bool $updateReturn = true, bool $insertReturn = true): AssetModel
    {
        $mock = $this->getMockBuilder(AssetModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update', 'insert'])
            ->getMock();

        $mock->method('find')->willReturn($findReturn);
        $mock->method('update')->willReturn($updateReturn);
        $mock->method('insert')->willReturn($insertReturn);

        return $mock;
    }

    private function mockLoanModel(bool $isAssetOut): LoanModel
    {
        $mock = $this->getMockBuilder(LoanModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAssetOut'])
            ->getMock();

        $mock->method('isAssetOut')->willReturn($isAssetOut);

        return $mock;
    }

    public function testDecommissionAssetThrowsWhenAssetNotFound(): void
    {
        $service = new AssetService(
            $this->mockAssetModel(null),
            $this->mockLoanModel(false)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Patrimônio não encontrado.');

        $service->decommissionAsset('invalid-id', 'Motivo qualquer');
    }

    public function testDecommissionAssetThrowsWhenAlreadyDecommissioned(): void
    {
        $service = new AssetService(
            $this->mockAssetModel(['id' => '123', 'decommissioned_at' => '2025-01-01 10:00:00']),
            $this->mockLoanModel(false)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Este patrimônio já está descomissionado.');

        $service->decommissionAsset('123', 'Motivo');
    }

    public function testDecommissionAssetThrowsWhenAssetIsOut(): void
    {
        $service = new AssetService(
            $this->mockAssetModel(['id' => '123', 'decommissioned_at' => null]),
            $this->mockLoanModel(true) // Asset is out
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Não é possível baixar um patrimônio que está atualmente emprestado.');

        $service->decommissionAsset('123', 'Motivo');
    }

    public function testDecommissionAssetSucceedsWhenValid(): void
    {
        $assetMock = $this->mockAssetModel(['id' => '123', 'decommissioned_at' => null], true);
        
        $assetMock->expects($this->once())
            ->method('update')
            ->with('123', $this->callback(function ($data) {
                return isset($data['decommissioned_at']) && $data['decommission_reason'] === 'Quebrou';
            }));

        $service = new AssetService($assetMock, $this->mockLoanModel(false));

        $service->decommissionAsset('123', 'Quebrou');
    }

    public function testCreateAssetInsertsData(): void
    {
        $assetMock = $this->mockAssetModel(null, true, true);
        
        $assetMock->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) {
                return isset($data['id']) && $data['name'] === 'Mesa' && $data['code'] === '001';
            }));

        $service = new AssetService($assetMock, $this->mockLoanModel(false));

        $service->createAsset([
            'parent_establishment_id' => 'abc',
            'name' => 'Mesa',
            'code' => '001',
            'type' => 'OWNED',
            'entry_date' => '2023-01-01',
        ]);
    }
}
