<?php

declare(strict_types=1);

namespace App\Enums;

enum AssetType: string
{
    case OWNED    = 'OWNED';
    case RENTED   = 'RENTED';
    case BORROWED = 'BORROWED';

    public function label(): string
    {
        return match($this) {
            self::OWNED    => 'Próprio',
            self::RENTED   => 'Alugado',
            self::BORROWED => 'Emprestado',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::OWNED    => 'bi-building',
            self::RENTED   => 'bi-receipt',
            self::BORROWED => 'bi-arrow-left-right',
        };
    }

    public function cssClass(): string
    {
        return match($this) {
            self::OWNED    => 'badge-owned',
            self::RENTED   => 'badge-rented',
            self::BORROWED => 'badge-borrowed',
        };
    }

    public function badge(): string
    {
        return sprintf(
            '<span class="badge-app %s"><i class="bi %s"></i>%s</span>',
            $this->cssClass(),
            $this->icon(),
            $this->label(),
        );
    }
}
