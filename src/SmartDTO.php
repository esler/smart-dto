<?php

namespace IW;

/**
 * Simple trait for creating "smart" data-transfer-objects.
 *
 * @author     Ondřej Ešler <ondrej.esler@intraworlds.com>
 * @version    SVN: $Id: $
 */
trait SmartDTO
{

    /**
     * Magic getter
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if (method_exists($this, $getter = 'get' . str_replace('_', '', $name))) {
            return $this->$getter();
        }

        if (property_exists($this, '_' . $name)) {
            return $this->{'_' . $name};
        }

        throw new \InvalidArgumentException('Trying to read unknown property "' . $name . '"');
    }

    /**
     * Magic isset
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name) {
        return property_exists($this, '_' . $name) || method_exists($this, 'get' . str_replace('_', '', $name));
    }

    /**
     * Magic setter
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value) {
        if (method_exists($this, $setter = 'set' . str_replace('_', '', $name))) {
            $this->$setter($value);
            return;
        }

        if (property_exists($this, '_' . $name)) {
            $this->{'_' . $name} = $value;
            return;
        }

        throw new \InvalidArgumentException('Trying write to unknown property "' . $name . '"');
    }

    /**
     * Fill this object with values given in associative array
     *
     * @param array $array
     *
     * @return void
     */
    public function hydrate(array $array): void {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Extracts this object into associative array
     *
     * @return mixed[]
     */
    public function extract(): array {
        $array = [];

        foreach (get_object_vars($this) as $name => $value) {
            $array[$name = ltrim($name, '_')] = $this->$name;
        }

        return $array;
    }
}
