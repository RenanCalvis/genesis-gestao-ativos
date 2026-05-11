<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\EstablishmentModel;
use App\Services\EstablishmentService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EstablishmentServiceTest extends CIUnitTestCase
{
    private function mockEstablishmentModel(?array $findReturn): EstablishmentModel
    {
        $mock = $this->getMockBuilder(EstablishmentModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find', 'update', 'insert'])
            ->getMock();

        $mock->method('find')->willReturn($findReturn);

        return $mock;
    }

    public function testCreateEstablishmentCleansCnpjAndCastsMaxLoanDays(): void
    {
        $mock = $this->mockEstablishmentModel(null);
        
        $mock->expects($this->once())
            ->method('insert')
            ->with($this->callback(function ($data) {
                return isset($data['id']) 
                    && $data['cnpj'] === '11222333000199' 
                    && $data['max_loan_days'] === 15;
            }));

        $service = new EstablishmentService($mock);

        $service->createEstablishment([
            'name' => 'Hospital Central',
            'cnpj' => '11.222.333/0001-99', 
            'type' => 'HOSPITAL',
            'max_loan_days' => '15' 
        ]);
    }

    public function testUpdateEstablishmentThrowsWhenNotFound(): void
    {
        $service = new EstablishmentService($this->mockEstablishmentModel(null));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Estabelecimento não encontrado.');

        $service->updateEstablishment('123', [
            'name' => 'X', 'cnpj' => '1', 'type' => 'Y', 'max_loan_days' => ''
        ]);
    }

    public function testUpdateEstablishmentSucceedsAndNullifiesEmptyMaxLoanDays(): void
    {
        $mock = $this->mockEstablishmentModel(['id' => '123']);
        
        $mock->expects($this->once())
            ->method('update')
            ->with('123', $this->callback(function ($data) {
                return $data['max_loan_days'] === null && $data['cnpj'] === '123';
            }));

        $service = new EstablishmentService($mock);

        $service->updateEstablishment('123', [
            'name' => 'Hospital Novo',
            'cnpj' => '1.2.3', 
            'type' => 'CLINICA',
            'max_loan_days' => '' 
        ]);
    }
}
