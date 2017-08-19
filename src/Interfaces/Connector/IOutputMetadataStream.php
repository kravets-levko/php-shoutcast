<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMetadataStream;

interface IOutputMetadataStream {

  public function getOutputMetadataStream(): IMetadataStream;

}
