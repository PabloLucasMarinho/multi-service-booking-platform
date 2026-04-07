<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash   = 'cash';
    case Credit = 'credit';
    case Debit  = 'debit';
    case Pix    = 'pix';

    public function label(): string
    {
        return match ($this) {
            self::Cash   => 'Dinheiro',
            self::Credit => 'Crédito',
            self::Debit  => 'Débito',
            self::Pix    => 'Pix',
        };
    }
}
