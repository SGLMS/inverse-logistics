<?php

namespace Sglms\InverseLogistics\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::Pending->value => __('Pending'),
            self::Approved->value => __('Approved'),
            self::Rejected->value => __('Rejected'),
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value] ?? $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            default => 'gray',
        };
    }
}
