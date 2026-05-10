<?php

declare(strict_types=1);

namespace App\Enums;

enum EstablishmentType: string
{
    case HOSPITAL    = 'HOSPITAL';
    case CLINICA     = 'CLINICA';
    case LABORATORIO = 'LABORATORIO';
    case AMBULATORIO = 'AMBULATORIO';

    public function label(): string
    {
        return match ($this) {
            self::HOSPITAL    => 'Hospital',
            self::CLINICA     => 'Clínica',
            self::LABORATORIO => 'Laboratório',
            self::AMBULATORIO => 'Ambulatório',
        };
    }

    public function cssClass(): string
    {
        return match ($this) {
            self::HOSPITAL    => 'badge-hospital',
            self::CLINICA     => 'badge-clinica',
            self::LABORATORIO => 'badge-laboratorio',
            self::AMBULATORIO => 'badge-ambulatorio',
        };
    }

    public function badge(): string
    {
        return sprintf(
            '<span class="badge-app %s">%s</span>',
            $this->cssClass(),
            $this->label()
        );
    }
}
