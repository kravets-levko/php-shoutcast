<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMediaSource;

interface IOutputMediaSource {

  public function getOutputMediaSource(): IMediaSource;

}
