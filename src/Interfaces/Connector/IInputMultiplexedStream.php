<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMultiplexedStream;

interface IInputMultiplexedStream {

  public function setInputMultiplexedStream(IMultiplexedStream $stream);

  public function clearInputMultiplexedStream();

}
