<?php

namespace EeObjects\Channels;

use ExpressionEngine\Model\Channel\Channel as ChanelModel;

class Channel
{
    protected $channel_id = 0;

    public function __construct(ChanelModel $channel = null)
    {
        if ($channel instanceof ChanelModel) {
            $this->init($channel);
        }
    }

    protected function init(ChanelModel $channel): void
    {
        $this->model = $channel;
        $this->channel_id = $channel->channel_id;
        $this->data = $channel->toArray();
    }

    public function getChannelId()
    {
        return $this->channel_id;
    }
}
