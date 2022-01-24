<?php

namespace EeObjects\Channels\Fields;

use EeObjects\Channels\AbstractField;

class Toggle extends AbstractField
{
    /**
     * @param $value
     * @return bool
     */
    public function read($value)
    {
        return bool_string($value);
    }
}
