<?php

namespace Eeobjects\Channels\Fields\Multi;

use Eeobjects\Channels\AbstractField;

class Select extends AbstractField
{
    /**
     * @param $value
     * @return string[]
     */
    public function read($value)
    {
        return $this->explodePipe($value);
    }

    /**
     * @param $value
     * @return bool|string
     */
    public function prepValueForStorage($value)
    {
        return implode('|', $value);
    }
}
