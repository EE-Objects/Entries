<?php

namespace EeObjects\Channels;

use ExpressionEngine\Model\Channel\ChannelEntry;

class Entries
{
    /**
     * @var Fields
     */
    protected $fields = null;

    /**
     * Returns the Fields object
     * @return Fields
     */
    protected function getFields()
    {
        if (is_null($this->fields)) {
            $this->fields = new Fields();
        }

        return $this->fields;
    }

    /**
     * Returns an Entry object
     * @param false $entry_id
     * @return Entries\Entry|null
     */
    public function getEntry($entry_id = false): ?Entries\Entry
    {
        $entry = ee('Model')
            ->get('ChannelEntry', $entry_id)
            ->with('Channel')
            ->first();

        if ($entry) {
            return $this->buildEntryObj($entry);
        }

        return null;
    }

    public function getBlankEntry(int $channel_id): Entries\Entry
    {
        $obj = new Entries\Entry();
        $obj->setChannelId($channel_id);

        return $obj->setFields($this->getFields());
    }

    /**
     * Generates the Entry object and injects the dependencies
     * @param ChannelEntry $entry
     * @return Entries\Entry
     */
    private function buildEntryObj(ChannelEntry $entry): Entries\Entry
    {
        $obj = new Entries\Entry($entry);
        $obj->setFields($this->getFields());

        return $obj;
    }
}
