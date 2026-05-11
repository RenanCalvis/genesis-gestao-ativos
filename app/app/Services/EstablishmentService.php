<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstablishmentModel;

class EstablishmentService
{
    public function __construct(
        private readonly EstablishmentModel $establishmentModel = new EstablishmentModel()
    ) {}

    public function createEstablishment(array $data): void
    {
        $this->establishmentModel->insert([
            'id'            => $this->generateUuid(),
            'name'          => $data['name'],
            'cnpj'          => $this->cleanCnpj($data['cnpj']),
            'type'          => $data['type'],
            'max_loan_days' => !empty($data['max_loan_days']) ? (int) $data['max_loan_days'] : null,
        ]);
    }

    public function updateEstablishment(string $id, array $data): void
    {
        $establishment = $this->establishmentModel->find($id);

        if ($establishment === null) {
            throw new \InvalidArgumentException('Estabelecimento não encontrado.');
        }

        $this->establishmentModel->update($id, [
            'name'          => $data['name'],
            'cnpj'          => $this->cleanCnpj($data['cnpj']),
            'type'          => $data['type'],
            'max_loan_days' => !empty($data['max_loan_days']) ? (int) $data['max_loan_days'] : null,
        ]);
    }

    private function cleanCnpj(string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }
}
