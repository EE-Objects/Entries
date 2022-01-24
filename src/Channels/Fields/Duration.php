<?php

namespace EeObjects\Channels\Fields;

use EeObjects\Channels\AbstractField;

class Duration extends AbstractField
{
    /**
     * @param mixed $value
     * @return float[]|int[]
     */
    public function read($value)
    {
        if ($value) {
            $minutes = $value;
            if (strpos($value, ':')) {
                $parts = explode(':', $value);
                $minutes = (int)$parts['1'] + ((int)$parts['0'] * 60);
            }

            return [
                'minutes' => $minutes,
            ];
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function prepValueForStorage($value)
    {
        if (isset($value['minutes'])) {
            return $value['minutes'];
        }
    }
}
