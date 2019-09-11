<?php

declare(strict_types=1);

// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,SlevomatCodingStandard.Classes.UnusedPrivateElements

namespace IW;

use function is_string;
use function json_decode;

final class UserDTO
{
    use SmartDTO;

    /** @var int */
    public $id;

    /** @var string */
    public $username;

    /** @var string */
    private $_role = 'member';

    /** @var mixed[] */
    private $_config = [];

    /**
     * Sets configuration
     *
     * @param string|mixed[] $value
     */
    private function setConfig($value) : void
    {
        if (is_string($value)) {
            $this->_config = json_decode($value, true);
        } else {
            $this->_config = $value;
        }
    }
}
