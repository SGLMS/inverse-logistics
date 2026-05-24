<?php

namespace Sglms\InverseLogistics\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Checkin = 'checkin';
    case Approved = 'approved';
    case Confirmed = 'confirmed';
    case Rejected = 'rejected';
    case Verified = 'verified';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return [
            self::Pending->value => __('Pending'),
            self::Checkin->value => __('Check-in'),
            self::Approved->value => __('Approved'),
            self::Confirmed->value => __('Confirmed'),
            self::Rejected->value => __('Rejected'),
            self::Verified->value => __('Verified'),
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
            self::Checkin => 'blue',
            self::Approved => 'green',
            self::Confirmed => 'green',
            self::Verified => 'indigo',
            self::Rejected => 'red',
            default => 'gray',
        };
    }

    public function editable(): bool
    {
        return match ($this) {
            self::Pending, self::Checkin => true,
            self::Approved, self::Rejected, self::Confirmed, self::Verified => false,
        };
    }
}
