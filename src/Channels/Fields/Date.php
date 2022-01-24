<?php

namespace EeObjects\Channels\Fields;

use EeObjects\Channels\AbstractField;

class Date extends AbstractField
{
    /**
     * @param $value
     * @return \DateTime|mixed
     */
    public function read($value)
    {
        $date = new \DateTime();

        return $date->setTimestamp($value);
    }

    public function prepValueForStorage($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('U');
        }
    }
}
