<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMediaSource;

interface IInputMediaSource {

  public function setInputMediaSource(IMediaSource $source);

  public function clearInputMediaSource();

}
