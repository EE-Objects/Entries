<?php

namespace EeObjects\Services;

use EeObjects\Channels;
use EeObjects\Channels\Entries\Entry;

class ChannelEntryService
{
    /**
     * The Channels object
     * @var Channels
     */
    protected $channels = null;

    /**
     * Returns the Channels object
     * @return Channels
     */
    protected function channels()
    {
        if (is_null($this->channels)) {
            $this->channels = new Channels();
        }

        return $this->channels;
    }

    /**
     * Returns the requested entry in a simple array
     * @param $entry_id
     * @return Entry|null
     */
    public function getEntry($entry_id): ?Entry
    {
        return $this->channels()->getEntry($entry_id);
    }

    /**
     * Returns a Blank Entry object
     * @param int $channel_id
     * @return Channels\Entries\Entry|null
     */
    public function getBlankEntry(int $channel_id)
    {
        return $this->channels()->getBlankEntry($channel_id);
    }

    /**
     * @param $short_name
     * @return Channels\Channel
     */
    public function getChannelByShortName($short_name)
    {
        return $this->channels()->getChannelByShortName($short_name);
    }

    public function getCategory()
    {

    }
}
