<?php

namespace EeObjects;

use EeObjects\Channels\Channel;
use EeObjects\Channels\Entries\Entry;
use ExpressionEngine\Model\Channel\Channel as ChanelModel;

class Channels
{
    /**
     * The Entries object
     * @var Channels\Entries
     */
    protected $entries = null;

    /**
     * Returns a specific Channel Entry object
     * @param $entry_id
     * @return Entry|null
     */
    public function getEntry($entry_id): ?Entry
    {
        return $this->getEntries()->getEntry($entry_id);
    }

    /**
     * @param array $where
     * @return Entry|null
     */
    public function getEntryWhere(array $where)
    {
        return $this->getEntries()->getEntryWhere($where);
    }

    /**
     * Returns a blank Channel Entry object
     * @param int $channel_id
     * @return Entry|null
     */
    public function getBlankEntry(int $channel_id): ?Entry
    {
        return $this->getEntries()->getBlankEntry($channel_id);
    }

    /**
     * Returns the Entries object
     * @return Channels\Entries
     */
    protected function getEntries()
    {
        if (is_null($this->entries)) {
            $this->entries = new Channels\Entries();
        }

        return $this->entries;
    }

    public function getChannelByShortName($short_name)
    {
        $channel = ee('Model')
            ->get('Channel')
            ->filter('channel_name', $short_name)
            ->first();

        if ($channel instanceof ChanelModel) {
            return $this->buildChannelObj($channel);
        }
    }

    private function buildChannelObj(ChanelModel $channel): Channels\Channel
    {
        $obj = new Channels\Channel($channel);

        return $obj;
    }
}
