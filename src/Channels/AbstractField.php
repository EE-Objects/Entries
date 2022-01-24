<?php

namespace EeObjects\Channels;

use EeObjects\Fields\AbstractField as EeObjectsAbstractField;
use ExpressionEngine\Model\Channel\ChannelField;

abstract class AbstractField extends EeObjectsAbstractField
{
    /**
     * The Entry this Field belongs to
     * @var int
     */
    protected $entry_id = 0;

    /**
     * Flag to tell the parser that the data is stored in channel_data
     * @var bool
     */
    protected $cd_storage = true;

    /**
     * @param int $entry_id
     * @return $this
     */
    public function setEntryId(int $entry_id): AbstractField
    {
        $this->entry_id = $entry_id;

        return $this;
    }

    /**
     * Returns the channel data label for the field
     * @return string
     */
    public function getRawColName(): string
    {
        return 'field_id_' . $this->getId();
    }

    /**
     * Whether the Field data is stored in channel_data table
     * @return bool
     */
    public function isCdStorage()
    {
        return $this->cd_storage;
    }

    /**
     * Will return a random sampling of the available
     *  value_label_pairs stored in Settings for the Field
     * @return array|int|string
     */
    public function randomOptionValues()
    {
        if (!$this->field_options) {
            if (is_array($this->setting('value_label_pairs')) && count($this->setting('value_label_pairs')) >= 1) {
                $this->field_options = $this->getValueLabelPairs();
            } else {
                $this->field_options = $this->getFieldListItems();
            }
        }

        if (!$this->field_options) {
            return [];
        }

        $total = rand(1, count($this->field_options));
        $data = array_rand($this->field_options, $total);
        if (!is_array($data)) {
            $data = [$data];
        }

        return $data;
    }

    /**
     * Returns the configured new line based key/value pairs
     * @return array
     */
    protected function getFieldListItems()
    {
        $field = ee('Model')->get('ChannelField', $this->getId())->first();
        if ($field instanceof ChannelField) {
            $items = explode("\n", $field->field_list_items);
            $return = [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $return[$item] = $item;
                }
            }

            return $return;
        }
    }
}
