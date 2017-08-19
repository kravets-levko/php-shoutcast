<?php

namespace Shoutcast\Interfaces\Connector;

use Shoutcast\Interfaces\Stream\IMetadataSource;

interface IInputMetadataSource {

  public function setInputMetadataSource(IMetadataSource $source);

  public function addInputMetadataSource(IMetadataSource $source);

  public function removeInputMetadataSource(IMetadataSource $source);

  public function clearInputMetadataSources();

}
