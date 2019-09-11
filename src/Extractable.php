<?php

declare(strict_types=1);

namespace IW;

/**
 * Defines
 */
interface Extractable
{
    /**
     * Extracts this object into associative array
     *
     * @return mixed[]
     */
    public function extract() : array;
}
