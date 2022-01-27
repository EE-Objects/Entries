<?php

namespace EeObjects\Channels\Entries;

use EeObjects\Channels\AbstractField;
use EeObjects\Channels\Fields;
use EeObjects\AbstractItem;
use EeObjects\Exceptions\Channels\Entries\EntryException;
use ExpressionEngine\Model\Channel\ChannelEntry;
use ExpressionEngine\Model\Member\Member as MemberModel;
use ExpressionEngine\Service\Model\Model as EeModel;

class Entry extends AbstractItem
{
    /**
     * The primary key for the Entry
     * @var int
     */
    protected $entry_id = 0;

    /**
     * The ExpressionEngine Channel this Entry belongs to
     * @var int
     */
    protected $channel_id = 0;

    /**
     * The shortname for the Channel we're under
     * @var string
     */
    protected $channel_name = [];

    /**
     * The Fields object
     * @var Fields
     */
    protected $fields = null;

    /**
     * Entry constructor.
     * @param ChannelEntry|null $entry
     */

    /**
     * Uses the ChannelEntry object to initialize our own object
     * @param ChannelEntry $entry
     */
    protected function init(EeModel $item): void
    {
        parent::init($item);
        $this->channel_id = $item->channel_id;
        $this->entry_id = $item->entry_id;
    }

    /**
     * Sets the Channel ID
     * @param int $channel_id
     * @return $this
     */
    public function setChannelId(int $channel_id): Entry
    {
        $this->channel_id = $channel_id;

        return $this;
    }

    /**
     * @param int $entry_id
     * @return $this
     */
    public function setEntryId(int $entry_id): Entry
    {
        $this->entry_id = $entry_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntryId()
    {
        return $this->entry_id;
    }

    /**
     * Returns the Channel ID used to determine fields
     * @return int
     * @throws EntryException
     */
    public function getChannelId(): int
    {
        if (!$this->channel_id) {
            throw new EntryException("Channel ID isn't setup!");
        }

        return $this->channel_id;
    }

    public function getChannelName()
    {
        if (empty($this->channel_name[$this->getChannelId()])) {
            $query = ee()->db->select('channel_name')->from('channels')->where(['channel_id' => $this->getChannelId()])->get();
            if ($query->result() && $query->num_rows() > 0) {
                $this->channel_name[$this->getChannelId()] = $query->row('channel_name');
            }
        }

        return $this->channel_name[$this->getChannelId()];
    }

    /**
     * The Channel Entries Fields object
     * @return Fields
     */
    public function getFields(): Fields
    {
        return $this->fields;
    }

    /**
     * Returns the Specific field object
     * @param $field_name
     * @return mixed
     */
    public function getField($field_name)
    {
        $field = $this->getFields()->getField($field_name, $this->getChannelId());
        if ($field instanceof AbstractField) {
            return $field->setEntryId($this->entry_id);
        }
    }

    /**
     * @param Fields $fields
     * @return $this
     */
    public function setFields(Fields $fields): Entry
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Saves the Entry object (create/update)
     * @return bool
     * @throws EntryException
     */
    public function save(): bool
    {
        if ($this->entry_id) {
            $return = $this->update();
        } else {
            $return = $this->create();
        }

        if (ee()->config->item('new_posts_clear_caches') == 'y') {
            ee()->functions->clear_caching('all');
        } else {
            ee()->functions->clear_caching('sql');
        }

        return $return;
    }

    /**
     * @return bool
     * @throws EntryException
     */
    protected function update(): bool
    {
        if (!$this->entry_id) {
            throw new EntryException("Entry ID isn't setup!");
        }

        foreach ($this->set_data as $field_name => $value) {
            $field = $this->getField($field_name);
            if ($field instanceof AbstractField) {
                if ($field->isCdStorage()) {
                    $field_name = $field->getRawColName();
                    $this->model->setRawProperty($field_name, $field->prepValueForStorage($value));
                }
            } else {
                if (isset($this->data[$field_name])) {
                    $this->model->setRawProperty($field_name, $value);
                }
            }
        }

        $return = false;
        if ($this->model->save() instanceof ChannelEntry) {
            foreach ($this->set_data as $field_name => $value) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->save($value);
                }
            }

            $this->renew();
            $return = true;
        }

        return $return;
    }

    /**
     * @throws EntryException
     */
    protected function create()
    {
        $this->setDefaults();
        foreach ($this->set_data as $field_name => $value) {
            $field = $this->getField($field_name);
            if ($field instanceof AbstractField) {
                if ($field->isCdStorage()) {
                    $field_name = $field->getRawColName();
                    $channel_titles[$field_name] = $field->prepValueForStorage($value);
                }
            } else {
                $channel_titles[$field_name] = $value;
            }
        }

        $channel = ee('Model')
            ->get('Channel', $this->getChannelId())
            ->filter('site_id', ee()->config->item('site_id'))
            ->first();

        $entry = ee('Model')->make('ChannelEntry');
        $entry->Channel = $channel;
        $entry->set($channel_titles);
        if ($entry->save() instanceof ChannelEntry) {
            $this->entry_id = $entry->entry_id;
            foreach ($this->set_data as $field_name => $value) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->save($value);
                }
            }

            $this->updateAuthorStats();
            $this->renew();

            return true;
        }

        throw new EntryException('Could not create Entry!');
    }

    /**
     * @throws EntryException
     */
    public function delete()
    {
        if ($this->model instanceof ChannelEntry) {
            $channel_fields = $this->getFields()->allFields($this->getChannelId());
            foreach ($channel_fields as $field_name => $field_details) {
                $field = $this->getField($field_name);
                if ($field instanceof AbstractField) {
                    $field->delete();
                }
            }

            $this->model->delete();
            $this->updateAuthorStats(false);
            $this->model = null;
            $this->channel_id = 0;
            $this->entry_id = 0;
        }
    }

    /**
     * Sets up the required details for an Entry, usually before writes
     * @throws EntryException
     * @return void
     */
    protected function setDefaults()
    {
        if (!$this->get('url_title')) {
            //@todo ensure uniqueness of url_title
            $this->set('url_title', ee('Format')->make('Text', $this->get('title'))->urlSlug()->compile());
        }

        if (!$this->get('status')) {
            $this->set('status', ee('Model')->get('Status', 1)->first()->status);
        }

        if (!$this->get('entry_date')) {
            $this->set('entry_date', ee()->localize->now);
        }

        $this->set('channel_id', $this->getChannelId());
        $this->set('year', date('Y', $this->get('entry_date')));
        $this->set('month', date('m', $this->get('entry_date')));
        $this->set('day', date('d', $this->get('entry_date')));
        $this->set('site_id', ee()->config->item('site_id'));
    }

    /**
     * Refreshes the existing object so it can be used repeatedly after writes
     * @throws EntryException
     * @return void
     */
    protected function renew(): void
    {
        if (!$this->entry_id) {
            throw new EntryException('Cannot renew Entry! Entry ID is missing!');
        }

        $entry = ee('Model')
            ->get('ChannelEntry', $this->entry_id)
            ->with('Channel')
            ->first();

        if (!$entry instanceof ChannelEntry) {
            throw new EntryException('Cannot renew Entry! Entry Missing from database!');
        }

        $this->init($entry);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->data;
        if ($this->getChannelId() && $this->model instanceof ChannelEntry) {
            $data = $this->getFields()->translateFieldData($this->model);
        } else {
            $data = $this->set_data;
        }

        return $data;
    }

    /**
     * Sets the Entry database model to use
     * @param ChannelEntry $model
     * @return $this
     */
    public function setModel(ChannelEntry $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Returns the specific data point or the default (if none)
     * @param $key string The shortname of the Field you want data for
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (isset($this->set_data[$key])) {
            return $this->set_data[$key]; //means was set() previously
        }

        //pull the data from the Field if we have one
        $field = $this->getField($key);
        if ($field instanceof AbstractField) {
            $field_key = $field->getRawColName();
            if (isset($this->data[$field_key])) {
                $default = $this->data[$field_key];
            }

            $this->set_data[$key] = $field->read($default);

            return $this->set_data[$key];
        }

        //default back to the converted channel data values
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Sets the data for the Entry
     * @param string $key
     * @param $value
     * @return $this
     */
    public function set(string $key, $value): Entry
    {
        $field = $this->getField($key);
        if ($field instanceof AbstractField) {
            $this->set_data[$key] = $value;
        } elseif ($this->getFields()->isCtValue($key)) {
            $this->set_data[$key] = $value;
        }

        return $this;
    }

    /**
     * Allows for setting a value based on the Field ID
     * @param $field_id
     * @param $value
     * @return $this
     */
    public function setFieldValue($field_id, $value): Entry
    {
        $field = $this->getFields()->getFieldById($field_id);
        if ($field instanceof AbstractField) {
            $this->set($field->getFieldName(), $value);
        }

        return $this;
    }

    /**
     * Returns the value of a Field based on its PK
     * @param $field_id
     * @return mixed|null
     */
    public function getFieldValue($field_id)
    {
        $field = $this->getFields()->getFieldById($field_id);
        if ($field instanceof AbstractField) {
            return $this->get($field->getRawColName());
        }
    }

    /**
     * Determines if a given field has already had data set to it
     * @param $field_name
     * @return bool
     */
    public function hasBeenSet($field_name)
    {
        return array_key_exists($field_name, $this->set_data);
    }

    /**
     * Updates the Author with entry total
     * @param bool $increment
     * @param int $amount
     */
    protected function updateAuthorStats($increment = true, $amount = 1): void
    {
        if ($this->get('author_id') && $this->entry_id) {
            $member = ee('Model')
                ->get('Member')
                ->filter('member_id', $this->get('author_id'))
                ->first();

            if ($member instanceof MemberModel) {
                $new_total = $member->total_entries + $amount;
                if (!$increment) {
                    $new_total = $member->total_entries - $amount;
                }
                $member->total_entries = $new_total;
                $member->save();
            }
        }
    }
}
