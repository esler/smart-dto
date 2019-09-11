<?php

declare(strict_types=1);

namespace IW;

interface Hydratable
{
    /**
     * Fill this object with values given in associative array
     *
     * @param mixed[] $array
     */
    public function hydrate(array $array) : void;
}
