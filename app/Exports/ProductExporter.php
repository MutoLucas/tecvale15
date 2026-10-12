<?php

namespace App\Exports;

use Illuminate\Support\Collection;

interface ProductExporter
{
    public function toCsv(Collection $products): string;

    public function toPdf(Collection $products): string;

    public function toXml(Collection $products): string;

    public function toJson(Collection $products): string;

    public function sendByEmail(Collection $products, string $email): void;
}
