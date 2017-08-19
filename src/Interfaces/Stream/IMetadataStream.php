<?php

namespace Shoutcast\Interfaces\Stream;

interface IMetadataStream {

  public function readMetadata(): string;

}
