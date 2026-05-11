<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EstablishmentModel;
use App\Services\EstablishmentService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class EstablishmentController extends BaseController
{
    private EstablishmentModel $establishmentModel;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->establishmentModel = new EstablishmentModel();
    }

    public function index(): string
    {
        $search = $this->request->getGet('search');

        $establishments = $this->establishmentModel->getPaginatedEstablishments(10, $search);
        $pager          = $this->establishmentModel->pager;

        return view('establishments/index', [
            'establishments' => $establishments,
            'pager'          => $pager,
            'search'         => $search,
        ]);
    }

    public function create(): ResponseInterface
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[5]',
                'errors' => [
                    'required'   => 'O Nome do Estabelecimento é obrigatório.',
                    'min_length' => 'O Nome do Estabelecimento deve ter pelo menos 5 caracteres.',
                ],
            ],
            'cnpj' => [
                'rules'  => 'required|exact_length[14]|is_unique[establishments.cnpj]',
                'errors' => [
                    'required'     => 'O CNPJ é obrigatório.',
                    'exact_length' => 'O CNPJ deve conter exatamente 14 dígitos válidos.',
                    'is_unique'    => 'Este CNPJ já está cadastrado em outra unidade.',
                ],
            ],
            'type' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Selecione o Tipo da unidade.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'erro',
                'message' => 'Verifique os campos obrigatórios.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        try {
            $service = new EstablishmentService();
            $service->createEstablishment($this->request->getPost());

            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 'sucesso',
                'message' => 'Estabelecimento cadastrado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[Est Create] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'erro',
                'message' => 'Erro interno ao cadastrar estabelecimento.',
            ]);
        }
    }

    public function update(string $id): ResponseInterface
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[5]',
                'errors' => [
                    'required'   => 'O Nome do Estabelecimento é obrigatório.',
                    'min_length' => 'O Nome do Estabelecimento deve ter pelo menos 5 caracteres.',
                ],
            ],
            'cnpj' => [
                'rules'  => "required|exact_length[14]|is_unique[establishments.cnpj,id,{$id}]",
                'errors' => [
                    'required'     => 'O CNPJ é obrigatório.',
                    'exact_length' => 'O CNPJ deve conter exatamente 14 dígitos válidos.',
                    'is_unique'    => 'Este CNPJ já está vinculado a outra unidade.',
                ],
            ],
            'type' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Selecione o Tipo da unidade.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'erro',
                'message' => 'Verifique os campos obrigatórios.',
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        try {
            $service = new EstablishmentService();
            $service->updateEstablishment($id, $this->request->getPost());

            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 'sucesso',
                'message' => 'Estabelecimento atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[Est Update] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'erro',
                'message' => 'Erro interno ao atualizar estabelecimento.',
            ]);
        }
    }
}
