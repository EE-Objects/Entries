<?php

namespace EeObjects\Channels;

use EeObjects\Fields\AbstractFields;
use EeObjects\Str;
use ExpressionEngine\Model\Channel\ChannelEntry;
use ExpressionEngine\Model\Channel\ChannelField as ChannelField;
use ExpressionEngine\Service\Model\Model;

class Fields extends AbstractFields
{
    /**
     * The shortname for where the parent Field group lives
     * @var bool
     */
    protected $config_domain = 'entry';

    /**
     * Contains a key/value store of channel_id => []fields for channel
     * @var array
     */
    protected $channel_fields = [];

    /**
     * The Channel Title safe values we bypass
     * @var array
     */
    protected $ct_fields = [
        'title',
        'site_id',
        'channel_id',
        'url_title',
        'ip_address',
        'author_id',
        'status_id',
        'status',
        'entry_date',
        'edit_date',
        'forum_topic_id',
        'year',
        'month',
        'day',
    ];

    /**
     * Converts a Channel Entry Model into a simple key=>value array of field mapping
     * @param ChannelEntry $entry
     * @return array
     */
    public function translateFieldData(Model $item): array
    {
        $data = $item->toArray();
        foreach ($this->getChannelFields($item->channel_id) as $field) {
            $field_type = $this->getFieldType($field['field_id'], $field['field_type'], $field['field_settings'], $field['field_name']);
            $key = 'field_id_' . $field['field_id'];
            if ($field_type instanceof AbstractField) {
                $data[$field['field_name']] = $field_type->setEntryId($item->entry_id)->read($data[$key]);
            } elseif (isset($data[$key])) {
                $data[$field['field_name']] = $data[$key];
            }
        }

        return $this->cleanUglyData($data);
    }

    /**
     * Determines if hte provided Field belongs to the Channel and thus assigned to the Entry
     * @param $channel_id
     * @param $field_name
     * @return bool
     */
    public function fieldExists($channel_id, $field_name)
    {
        $fields = $this->getChannelFields($channel_id);

        return isset($fields[$field_name]);
    }

    /**
     * Returns the Field object
     * @param string $field_name
     * @param int $channel_id
     * @return AbstractField|null
     */
    public function getField(string $field_name, int $channel_id): ?AbstractField
    {
        if ($this->fieldExists($channel_id, $field_name)) {
            $field = $this->channel_fields[$channel_id][$field_name];

            return $this->getFieldType($field['field_id'], $field['field_type'], $field['field_settings'], $field['field_name']);
        }

        return null;
    }

    /**
     * Returns the Field object by its ID
     * @param int $field_id
     * @return AbstractField|null
     */
    public function getFieldById(int $field_id)
    {
        $field = ee('Model')->get('ChannelField', $field_id)->first();
        if ($field instanceof ChannelField) {
            return $this->getFieldType($field->field_id, $field->field_type, $field->field_settings, $field->field_name);
        }
    }

    /**
     * Removes the redundant data from the channel_data table
     * @param array $data
     * @return array
     */
    protected function cleanUglyData(array $data): array
    {
        //remove ugly keys
        foreach ($data as $key => $value) {
            if (strstr($key, 'field_id_') ||
                strstr($key, 'field_ft_') ||
                strstr($key, 'field_dt_')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Returns the specific Channel Fields assigned to a given Channel ID
     * @param $channel_id
     * @return array
     */
    protected function getChannelFields(int $channel_id): array
    {
        if (!isset($this->channel_fields[$channel_id])) {
            $channels = ee('Model')->get('Channel', $channel_id)->first();
            $fieldsarr = [];
            foreach ($channels->getAllCustomFields() as $field) {
                $fieldsarr[$field->field_name] = [
                    'field_id' => $field->field_id,
                    'field_name' => $field->field_name,
                    'field_type' => $field->field_type,
                    'field_label' => $field->field_label,
                    'field_settings' => $field->field_settings,
                    'field_fmt' => $field->field_fmt,
                ];
            }

            $this->channel_fields[$channel_id] = $fieldsarr;
        }

        return $this->channel_fields[$channel_id];
    }

    /**
     * Returns all the fields based on the Channel
     * @param int $channel_id
     * @return array
     */
    public function allFields(int $channel_id): array
    {
        return $this->getChannelFields($channel_id);
    }

    /**
     * @param string $field_type
     * @param array $field_settings
     * @return AbstractField|null
     */
    protected function getFieldType(int $field_id, string $field_type, array $field_settings, $field_name = false): ?AbstractField
    {
        $obj_name = Str::dash2ns($field_type);
        $obj = 'EeObjects\Channels\Fields\\' . $obj_name;

        if (!class_exists($obj)) {
            $providers = ee('App')->getProviders();
            foreach ($providers as $providerKey => $provider) {
                if ($provider->get('ee_objects')) {
                    $config = $provider->get('ee_objects');
                    if (!empty($config['fields']) && is_array($config['fields'])) {
                        if (isset($config['fields'][$field_type])) {
                            $obj = $config['fields'][$field_type];
                            break;
                        }
                    }
                }
            }
        }

        if (class_exists($obj)) {
            $class = new $obj();
            if ($class instanceof AbstractField) {
                $class->setFieldId($field_id)
                    ->setFieldName($field_name)
                    ->setSettings($field_settings);

                return $class;
            }
        }

        return null;
    }

    /**
     * Determines if the given Field is a part of the channel_titles data set
     * @param $key
     * @return bool
     */
    public function isCtValue($key)
    {
        return in_array($key, $this->ct_fields);
    }

    public function canOverRide($channel_id, $field_name)
    {
    }
}
