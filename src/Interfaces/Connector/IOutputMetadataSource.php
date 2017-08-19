<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMetadataSource;

interface IOutputMetadataSource {

  public function getOutputMetadataSource(): IMetadataSource;

}
