<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LoanModel;
use App\Services\LoanService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class LoanController extends BaseController
{
    private LoanModel $loanModel;
    private LoanService $loanService;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->loanModel   = new LoanModel();
        $this->loanService = new LoanService();
    }

    public function index(): string
    {
        $loans = $this->loanModel->findAllWithRelations();

        return view('loans/index', [
            'loans' => $loans
        ]);
    }

    public function createLoan(): ResponseInterface
    {
        $rules = [
            'asset_id'                   => 'required',
            'requester_establishment_id' => 'required',
            'lender_establishment_id'    => 'required',
            'checked_out_at'             => 'required|valid_date[Y-m-d H:i:s]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => 'Dados inválidos ou incompletos.',
                    'errors'  => $this->validator->getErrors(),
                ]);
        }

        try {
            $loan = $this->loanService->createLoan(
                $this->request->getPost('requester_establishment_id'),
                $this->request->getPost('lender_establishment_id'),
                $this->request->getPost('asset_id'),
                $this->request->getPost('checked_out_at'),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => $e->getMessage(),
                ]);
        } catch (\RuntimeException $e) {
            $this->logger->error('[Loan Create] ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => 'Erro interno ao registrar o empréstimo.',
                ]);
        }

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status'   => 'sucesso',
                'message'  => 'Empréstimo registrado com sucesso.',
                'due_date' => $loan['due_date'],
                'loan_id'  => $loan['id'],
            ]);
    }
}
