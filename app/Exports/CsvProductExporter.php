<?php

namespace App\Exports;

use Exception;
use Illuminate\Support\Collection;

class CsvProductExporter implements ProductExporter
{
    public function toCsv(Collection $products): string
    {
        $lines = [];
        $lines[] = 'SKU;Nome;Categoria;Tipo;Preço;Estoque;Ativo';

        foreach ($products as $product) {
            $lines[] = implode(';', [
                $product->sku,
                $product->name,
                $product->category ? $product->category->name : '-',
                $product->type,
                number_format($product->price, 2, ',', '.'),
                $product->stock,
                $product->active ? 'Sim' : 'Não',
            ]);
        }

        return implode("\n", $lines);
    }

    public function toPdf(Collection $products): string
    {
        throw new Exception('Não suportado.');
    }

    public function toXml(Collection $products): string
    {
        throw new Exception('Não suportado.');
    }

    public function toJson(Collection $products): string
    {
        return '';
    }

    public function sendByEmail(Collection $products, string $email): void
    {
        throw new Exception('Não suportado.');
    }
}
