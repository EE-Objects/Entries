<?php

namespace EeObjects\Channels\Fields;

use EeObjects\Channels\AbstractField;
use ExpressionEngine\Model\File\File as FileModel;

class File extends AbstractField
{
    /**
     * Will Returns the file_id for the variable
     * @param mixed $value
     * @return int|mixed
     */
    public function read($value)
    {
        if (preg_match('/^{filedir_(\d+)}/', $value, $matches)) {
            $upload_location_id = $matches[1];
            $file_name = str_replace($matches[0], '', $value);
            $file = ee('Model')->get('File')
                ->filter('site_id', ee()->config->item('site_id'))
                ->filter('upload_location_id', $upload_location_id)
                ->filter('file_name', $file_name)
                ->first();

            if ($file instanceof FileModel) {
                return $file->getId();
            }
        }
    }

    /**
     * Prepares the file_id for storage as a variable
     * @param $value
     * @return string
     */
    public function prepValueForStorage($value)
    {
        $file = ee('Model')->get('File')
            ->filter('site_id', ee()->config->item('site_id'))
            ->filter('file_id', $value)
            ->first();

        if ($file instanceof FileModel) {
            return '{filedir_' . $file->upload_location_id . '}' . $file->file_name;
        }
    }
}
