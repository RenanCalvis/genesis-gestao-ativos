<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\EstablishmentModel;
use App\Services\AssetService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use \Psr\Log\LoggerInterface;

class AssetController extends BaseController
{
    private AssetModel $assetModel;
    private EstablishmentModel $establishmentModel;
    private AssetService $assetService;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->assetModel         = new AssetModel();
        $this->establishmentModel = new EstablishmentModel();
        $this->assetService       = new AssetService();
    }

    public function index(): string
    {
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $assets = $this->assetModel->getPaginatedAssets(10, $search, $status);
        $pager  = $this->assetModel->pager;

        $establishments = $this->establishmentModel->findAll();

        return view('assets/index', [
            'assets'         => $assets,
            'pager'          => $pager,
            'search'         => $search,
            'status'         => $status,
            'establishments' => $establishments,
        ]);
    }

    public function decommission(string $id): ResponseInterface
    {
        $rules = [
            'decommission_reason' => [
                'rules'  => 'required|min_length[10]',
                'errors' => [
                    'required'   => 'O Motivo da Baixa é obrigatório.',
                    'min_length' => 'O Motivo da Baixa deve ter ao menos 10 caracteres.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => 'O motivo da baixa é obrigatório e deve ter ao menos 10 caracteres.',
                    'errors'  => $this->validator->getErrors(),
                ]);
        }

        try {
            $this->assetService->decommissionAsset(
                $id,
                $this->request->getPost('decommission_reason')
            );
        } catch (\InvalidArgumentException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'erro',
                    'message' => $e->getMessage(),
                ]);
        }

        return $this->response
            ->setStatusCode(200)
            ->setJSON([
                'status'  => 'sucesso',
                'message' => 'Patrimônio descomissionado com sucesso.',
            ]);
    }

    public function create(): ResponseInterface
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[5]',
                'errors' => [
                    'required'   => 'O campo Nome do Item é obrigatório.',
                    'min_length' => 'O Nome do Item deve ter pelo menos 5 caracteres.',
                ],
            ],
            'code' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'O campo Código/Tag é obrigatório.',
                    'min_length' => 'O Código/Tag deve ter pelo menos 3 caracteres.',
                ],
            ],
            'type' => [
                'rules'  => 'required|in_list[OWNED,RENTED,BORROWED]',
                'errors' => [
                    'required' => 'O campo Situação (Tipo) é obrigatório.',
                    'in_list'  => 'A Situação selecionada é inválida.',
                ],
            ],
            'parent_establishment_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'A Unidade de Origem é obrigatória.',
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
            $this->assetService->createAsset($this->request->getPost());
            
            return $this->response->setStatusCode(201)->setJSON([
                'status'  => 'sucesso',
                'message' => 'Patrimônio cadastrado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[Asset Create] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'erro',
                'message' => 'Erro interno ao cadastrar patrimônio.',
            ]);
        }
    }

    public function update(string $id): ResponseInterface
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[5]',
                'errors' => [
                    'required'   => 'O campo Nome do Item é obrigatório.',
                    'min_length' => 'O Nome do Item deve ter pelo menos 5 caracteres.',
                ],
            ],
            'code' => [
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'O campo Código/Tag é obrigatório.',
                    'min_length' => 'O Código/Tag deve ter pelo menos 3 caracteres.',
                ],
            ],
            'type' => [
                'rules'  => 'required|in_list[OWNED,RENTED,BORROWED]',
                'errors' => [
                    'required' => 'O campo Situação (Tipo) é obrigatório.',
                    'in_list'  => 'A Situação selecionada é inválida.',
                ],
            ],
            'parent_establishment_id' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'A Unidade de Origem é obrigatória.',
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
            $this->assetService->updateAsset($id, $this->request->getPost());
            
            return $this->response->setStatusCode(200)->setJSON([
                'status'  => 'sucesso',
                'message' => 'Patrimônio atualizado com sucesso!',
            ]);
        } catch (\Exception $e) {
            $this->logger->error('[Asset Update] ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'erro',
                'message' => 'Erro interno ao atualizar patrimônio.',
            ]);
        }
    }
}
