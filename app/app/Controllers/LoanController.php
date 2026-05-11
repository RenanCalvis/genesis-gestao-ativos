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
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $loans = $this->loanModel->getPaginatedLoans(10, $search, $status);
        $pager = $this->loanModel->pager;

        return view('loans/index', [
            'loans'  => $loans,
            'pager'  => $pager,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function createLoan(): ResponseInterface
    {
        $rules = [
            'asset_id'                   => 'required',
            'requester_establishment_id' => 'required',
            'lender_establishment_id'    => 'required',
            'checked_out_at'             => 'required|valid_date[Y-m-d H:i:s]',
            'due_date'                   => 'required|valid_date[Y-m-d H:i:s]',
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

        $checkedOutAt = $this->request->getPost('checked_out_at');
        $dueDate      = $this->request->getPost('due_date');

        if (strtotime($dueDate) <= strtotime($checkedOutAt)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => 'A data prevista de devolução deve ser posterior à data de retirada.',
                ]);
        }

        try {
            $loan = $this->loanService->createLoan(
                $this->request->getPost('requester_establishment_id'),
                $this->request->getPost('lender_establishment_id'),
                $this->request->getPost('asset_id'),
                $checkedOutAt,
                $dueDate
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

    public function returnLoan(string $id): ResponseInterface
    {
        try {
            $this->loanService->returnLoan($id);
        } catch (\InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => $e->getMessage(),
                ]);
        } catch (\RuntimeException $e) {
            $this->logger->error('[Loan Return] ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => 'Erro interno ao processar a devolução.',
                ]);
        }

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status'  => 'sucesso',
                'message' => 'Patrimônio devolvido com sucesso.',
            ]);
    }
}
