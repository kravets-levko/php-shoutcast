<?php

namespace Shoutcast\Filter\Metadata;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Stream\IMetadataSource;

class Defaults extends Filter implements IOutputMetadataSource, IMetadataSource {

  static protected $defaultMetadata = [
    'artist' => 'Unknown artist',
    'title' => 'Unknown track',
    'duration' => -1,
  ];

  public function getOutputMetadataSource(): IMetadataSource {
    return $this;
  }

  public function getMetadata(): array {
    return array_merge(static::$defaultMetadata, $this -> config);
  }

  public function getArtist(): string {
    $metadata = array_merge(static::$defaultMetadata, $this -> getMetadata());
    return $metadata['artist'];
  }

  public function getTitle(): string {
    $metadata = array_merge(static::$defaultMetadata, $this -> getMetadata());
    return $metadata['title'];
  }

  public function getDuration(): int {
    $metadata = array_merge(static::$defaultMetadata, $this -> getMetadata());
    return $metadata['duration'];
  }

}
