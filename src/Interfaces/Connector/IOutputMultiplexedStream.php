<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMultiplexedStream;

interface IOutputMultiplexedStream {

  public function getOutputMultiplexedStream(): IMultiplexedStream;

}
