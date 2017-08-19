<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMetadataStream;

interface IInputMetadataStream {

  public function setInputMetadataStream(IMetadataStream $stream);

  public function clearInputMetadataStream();

}
