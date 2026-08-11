<?php

declare(strict_types=1);

namespace ExAkt\CtmHeader;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ExAktCtmHeader extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
