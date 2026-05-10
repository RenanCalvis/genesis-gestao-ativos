<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EstablishmentModel;
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
        $establishments = $this->establishmentModel
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return view('establishments/index', [
            'establishments' => $establishments,
        ]);
    }
}
