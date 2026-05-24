<?php

namespace Sglms\InverseLogistics\Models\Traits;

trait ConfigureModels
{
    protected function modelClass(string $name, string $fallback): string
    {
        return (string) config("inverse-logistics.models.{$name}", $fallback);
    }
}
