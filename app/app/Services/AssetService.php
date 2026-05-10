<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AssetModel;
use InvalidArgumentException;

class AssetService
{
    public function __construct(
        private readonly AssetModel $assetModel = new AssetModel(),
    ) {}

    public function decommissionAsset(string $assetId, string $reason): void
    {
        $asset = $this->assetModel->find($assetId);

        if ($asset === null) {
            throw new InvalidArgumentException('Patrimônio não encontrado.');
        }

        if ($asset['decommissioned_at'] !== null) {
            throw new InvalidArgumentException('Este patrimônio já está descomissionado.');
        }

        $this->assetModel->update($assetId, [
            'decommissioned_at'   => date('Y-m-d H:i:s'),
            'decommission_reason' => $reason,
        ]);
    }
}
