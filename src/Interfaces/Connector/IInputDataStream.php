<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IDataStream;

interface IInputDataStream {

  public function setInputDataStream(IDataStream $stream);

  public function clearInputDataStream();

}
