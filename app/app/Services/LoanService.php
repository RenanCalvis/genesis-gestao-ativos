<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AssetModel;
use App\Models\EstablishmentModel;
use App\Models\LoanModel;
use InvalidArgumentException;
use RuntimeException;

class LoanService
{
    public function __construct(
        private readonly EstablishmentModel $establishmentModel = new EstablishmentModel(),
        private readonly AssetModel $assetModel = new AssetModel(),
        private readonly LoanModel $loanModel = new LoanModel(),
    ) {}

    public function createLoan(
        string $requesterEstablishmentId,
        string $lenderEstablishmentId,
        string $assetId,
        string $checkedOutAt,
        string $dueDate,
    ): array {
        if ($requesterEstablishmentId === $lenderEstablishmentId) {
            throw new InvalidArgumentException(
                'Uma unidade não pode realizar um empréstimo para si mesma.'
            );
        }
        $requester = $this->establishmentModel->find($requesterEstablishmentId);
        $lender    = $this->establishmentModel->find($lenderEstablishmentId);
        $asset     = $this->assetModel->find($assetId);

        if ($requester === null) {
            throw new InvalidArgumentException('Estabelecimento solicitante não encontrado.');
        }
        if ($lender === null) {
            throw new InvalidArgumentException('Estabelecimento atendente não encontrado.');
        }
        if ($asset === null) {
            throw new InvalidArgumentException('Patrimônio não encontrado.');
        }

        if ($requester['type'] !== $lender['type']) {
            throw new InvalidArgumentException(
                'Empréstimo negado: os estabelecimentos solicitante e atendente são de tipos diferentes.'
            );
        }

        if ($asset['decommissioned_at'] !== null) {
            throw new InvalidArgumentException(
                'Empréstimo negado: o patrimônio está descomissionado e não pode ser movimentado.'
            );
        }


        if ($this->loanModel->isAssetOut($assetId)) {
            throw new InvalidArgumentException(
                'Empréstimo negado: o patrimônio já está em uso por outro estabelecimento e ainda não foi devolvido.'
            );
        }

        $maxLoanDays = $lender['max_loan_days'] !== null ? (int) $lender['max_loan_days'] : null;

        if ($maxLoanDays !== null) {
            $diffSeconds = strtotime($dueDate) - strtotime($checkedOutAt);
            $diffDays    = (int) ceil($diffSeconds / 86400);

            if ($diffDays > $maxLoanDays) {
                throw new InvalidArgumentException(
                    "A data prevista excede o limite máximo de empréstimo ({$maxLoanDays} dias) permitido pela unidade de origem."
                );
            }
        }

        $loanId = $this->generateUuid();

        $loan = [
            'id'                         => $loanId,
            'requester_establishment_id' => $requesterEstablishmentId,
            'lender_establishment_id'    => $lenderEstablishmentId,
            'asset_id'                   => $assetId,
            'checked_out_at'             => $checkedOutAt,
            'due_date'                   => $dueDate,
            'returned_at'                => null,
        ];

        if ($this->loanModel->insert($loan) === false) {
            throw new RuntimeException('Falha ao registrar o empréstimo no banco de dados.');
        }

        return $this->loanModel->find($loanId);
    }

    public function returnLoan(string $loanId): void
    {
        $loan = $this->loanModel->find($loanId);

        if ($loan === null) {
            throw new InvalidArgumentException('Empréstimo não encontrado.');
        }

        if ($loan['returned_at'] !== null) {
            throw new InvalidArgumentException('Este empréstimo já foi devolvido.');
        }

        if ($this->loanModel->update($loanId, ['returned_at' => date('Y-m-d H:i:s')]) === false) {
            throw new RuntimeException('Falha ao registrar a devolução no banco de dados.');
        }
    }

    private function generateUuid(): string
    {
        // geracao basica rfc 4122 v4
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
