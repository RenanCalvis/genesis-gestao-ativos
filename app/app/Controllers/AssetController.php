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
        $assets         = $this->assetModel->findAllWithEstablishments();
        $establishments = $this->establishmentModel->findAll();

        return view('assets/index', [
            'assets'         => $assets,
            'establishments' => $establishments,
        ]);
    }

    public function decommission(string $id): ResponseInterface
    {
        $rules = [
            'decommission_reason' => 'required|min_length[10]',
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
}
