<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AssetModel;
use App\Models\EstablishmentModel;
use App\Models\LoanModel;
use App\Services\LoanService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class LoanServiceTest extends CIUnitTestCase
{
    private LoanService $service;

    private function makeEstablishment(string $type, ?int $maxLoanDays = 10): array
    {
        return [
            'id'            => 'aaaaaaaa-0000-4000-8000-000000000001',
            'name'          => 'Estabelecimento Teste',
            'cnpj'          => '11111111000191',
            'type'          => $type,
            'max_loan_days' => $maxLoanDays,
            'deleted_at'    => null,
        ];
    }

    private function makeActiveAsset(): array
    {
        return [
            'id'                      => 'bbbbbbbb-0000-4000-8000-000000000001',
            'parent_establishment_id' => 'aaaaaaaa-0000-4000-8000-000000000001',
            'name'                    => 'Desfibrilador Padrão',
            'code'                    => 'PAT-TEST-001',
            'type'                    => 'OWNED',
            'entry_date'              => '2023-01-10 08:00:00',
            'decommissioned_at'       => null,
            'decommission_reason'     => null,
        ];
    }

    private function makeDecommissionedAsset(): array
    {
        return array_merge($this->makeActiveAsset(), [
            'decommissioned_at'   => '2024-11-20 17:30:00',
            'decommission_reason' => 'Placa lógica queimada.',
        ]);
    }

    // mock establishment find
    private function mockEstablishments(array $byId): EstablishmentModel
    {
        $mock = $this->getMockBuilder(EstablishmentModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $mock->method('find')->willReturnCallback(
            static fn(string $id) => $byId[$id] ?? null
        );

        return $mock;
    }

    // mock asset find
    private function mockAsset(?array $asset): AssetModel
    {
        $mock = $this->getMockBuilder(AssetModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();

        $mock->method('find')->willReturn($asset);

        return $mock;
    }

    // mock loan insert e find
    private function mockLoanSuccess(array $loanReturn): LoanModel
    {
        $mock = $this->getMockBuilder(LoanModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insert', 'find'])
            ->getMock();

        $mock->method('insert')->willReturn(true);
        $mock->method('find')->willReturn($loanReturn);

        return $mock;
    }

    // teste de caminho feliz

    public function testCreateLoanSucceedsWhenEstablishmentsHaveSameTypeAndAssetIsActive(): void
    {
        // Arrange
        $requester = $this->makeEstablishment('CLINICA', 10);
        $lender    = $this->makeEstablishment('CLINICA', 10);
        $lender['id'] = 'aaaaaaaa-0000-4000-8000-000000000002';

        $asset       = $this->makeActiveAsset();
        $checkedOut  = '2025-06-01 08:00:00';
        $expectedDue = '2025-06-11 08:00:00'; // +10 dias

        $expectedLoan = [
            'id'                         => 'cccccccc-0000-4000-8000-000000000001',
            'requester_establishment_id' => $requester['id'],
            'lender_establishment_id'    => $lender['id'],
            'asset_id'                   => $asset['id'],
            'checked_out_at'             => $checkedOut,
            'due_date'                   => $expectedDue,
            'returned_at'                => null,
        ];

        $establishmentMock = $this->mockEstablishments([
            $requester['id'] => $requester,
            $lender['id']    => $lender,
        ]);
        $assetMock = $this->mockAsset($asset);
        $loanMock  = $this->mockLoanSuccess($expectedLoan);

        $service = new LoanService($establishmentMock, $assetMock, $loanMock);

        // Act
        $result = $service->createLoan(
            $requester['id'],
            $lender['id'],
            $asset['id'],
            $checkedOut,
        );

        // Assert
        $this->assertSame($expectedLoan['due_date'], $result['due_date']);
        $this->assertSame($expectedLoan['id'], $result['id']);
        $this->assertNull($result['returned_at']);
    }

    // test guard tipos incompativeis

    public function testCreateLoanThrowsWhenEstablishmentsHaveDifferentTypes(): void
    {
        // Arrange
        $requester = $this->makeEstablishment('HOSPITAL');
        $lender    = $this->makeEstablishment('CLINICA');
        $lender['id'] = 'aaaaaaaa-0000-4000-8000-000000000002';

        $asset = $this->makeActiveAsset();

        $service = new LoanService(
            $this->mockEstablishments([
                $requester['id'] => $requester,
                $lender['id']    => $lender,
            ]),
            $this->mockAsset($asset),
            $this->getMockBuilder(LoanModel::class)->disableOriginalConstructor()->getMock(),
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/tipos diferentes/i');

        // Act
        $service->createLoan($requester['id'], $lender['id'], $asset['id'], '2025-06-01 08:00:00');
    }

    // test guard patrimonio baixado

    public function testCreateLoanThrowsWhenAssetIsDecommissioned(): void
    {
        // Arrange
        $requester = $this->makeEstablishment('CLINICA');
        $lender    = $this->makeEstablishment('CLINICA');
        $lender['id'] = 'aaaaaaaa-0000-4000-8000-000000000002';

        $asset = $this->makeDecommissionedAsset();

        $service = new LoanService(
            $this->mockEstablishments([
                $requester['id'] => $requester,
                $lender['id']    => $lender,
            ]),
            $this->mockAsset($asset),
            $this->getMockBuilder(LoanModel::class)->disableOriginalConstructor()->getMock(),
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/descomissionado/i');

        // Act
        $service->createLoan($requester['id'], $lender['id'], $asset['id'], '2025-06-01 08:00:00');
    }

    // test calculo de sla com limite definido

    public function testCalculateDueDateRespectsLenderMaxLoanDays(): void
    {
        // Arrange
        $service     = new LoanService();
        $checkedOut  = '2025-07-01 00:00:00';
        $maxLoanDays = 5;

        // Act
        $dueDate = $service->calculateDueDate($checkedOut, $maxLoanDays);

        // Assert
        $this->assertSame('2025-07-06 00:00:00', $dueDate);
    }

    public function testCalculateDueDateFallsBackTo365DaysWhenMaxLoanDaysIsNull(): void
    {
        // Arrange
        $service    = new LoanService();
        $checkedOut = '2025-01-01 00:00:00';

        // Act
        $dueDate = $service->calculateDueDate($checkedOut, null);

        // assert 365 dias depois
        $expected = date('Y-m-d H:i:s', strtotime($checkedOut . ' +365 days'));
        $this->assertSame($expected, $dueDate);
    }

    // test entidades inexistentes

    public function testCreateLoanThrowsWhenRequesterNotFound(): void
    {
        // Arrange
        $service = new LoanService(
            $this->mockEstablishments([]),    // nenhum estabelecimento cadastrado
            $this->mockAsset($this->makeActiveAsset()),
            $this->getMockBuilder(LoanModel::class)->disableOriginalConstructor()->getMock(),
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/solicitante não encontrado/i');

        // Act
        $service->createLoan('id-inexistente', 'id-qualquer', 'asset-id', '2025-06-01 08:00:00');
    }

    public function testCreateLoanThrowsWhenAssetNotFound(): void
    {
        // Arrange
        $requester = $this->makeEstablishment('CLINICA');
        $lender    = $this->makeEstablishment('CLINICA');
        $lender['id'] = 'aaaaaaaa-0000-4000-8000-000000000002';

        $service = new LoanService(
            $this->mockEstablishments([
                $requester['id'] => $requester,
                $lender['id']    => $lender,
            ]),
            $this->mockAsset(null),    // asset não encontrado
            $this->getMockBuilder(LoanModel::class)->disableOriginalConstructor()->getMock(),
        );

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Patrimônio não encontrado/i');

        // Act
        $service->createLoan($requester['id'], $lender['id'], 'asset-id-invalido', '2025-06-01 08:00:00');
    }
}
