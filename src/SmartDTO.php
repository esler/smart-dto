<?php

declare(strict_types=1);

namespace IW;

use InvalidArgumentException;
use function get_object_vars;
use function ltrim;
use function method_exists;
use function property_exists;
use function str_replace;

/**
 * Simple trait for creating "smart" data-transfer-objects.
 */
trait SmartDTO
{
    /**
     * Magic getter
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $getter = 'get' . str_replace('_', '', $name);

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        if (property_exists($this, '_' . $name)) {
            return $this->{'_' . $name};
        }

        throw new InvalidArgumentException('Trying to read unknown property "' . $name . '"');
    }

    /**
     * Magic isset
     */
    public function __isset(string $name) : bool
    {
        return property_exists($this, '_' . $name) || method_exists($this, 'get' . str_replace('_', '', $name));
    }

    /**
     * Magic setter
     *
     * @param mixed $value
     */
    public function __set(string $name, $value) : void
    {
        $setter = 'set' . str_replace('_', '', $name);

        if (method_exists($this, $setter)) {
            $this->$setter($value);

            return;
        }

        if (property_exists($this, '_' . $name)) {
            $this->{'_' . $name} = $value;

            return;
        }

        throw new InvalidArgumentException('Trying write to unknown property "' . $name . '"');
    }

    /**
     * Fill this object with values given in associative array
     *
     * @param mixed[] $array
     */
    public function hydrate(array $array) : void
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Extracts this object into associative array
     *
     * @return mixed[]
     */
    public function extract() : array
    {
        $array = [];

        foreach (get_object_vars($this) as $name => $value) {
            $array[$name = ltrim($name, '_')] = $this->$name;
        }

        return $array;
    }
}
