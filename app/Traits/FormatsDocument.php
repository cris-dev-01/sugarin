<?php

declare(strict_types=1);

namespace App\Traits;

use Freshwork\ChileanBundle\Rut;

trait FormatsDocument
{
    protected function sanitizeDocumentWithoutVerificator(string $document): string
    {
        return Rut::parse($document)->toArray()[0];
    }

    protected function formatDocumentWithCalculatedVerificator(string $document): string
    {
        return Rut::set($document)->fix()->format(Rut::FORMAT_COMPLETE);
    }
}
