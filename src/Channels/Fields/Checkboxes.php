<?php

namespace EeObjects\Channels\Fields;

use Eeobjects\Channels\AbstractField;

class Checkboxes extends AbstractField
{
    /**
     * @param mixed $value
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
