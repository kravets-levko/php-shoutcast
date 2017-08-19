<?php

namespace Shoutcast\Filter;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputMetadataStream;
use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataStream;
use Shoutcast\Interfaces\Stream\IMetadataSource;
use Shoutcast\Interfaces\Stream\IMetadataStream;

class MetadataSource extends Filter implements IInputMetadataStream, IOutputMetadataStream,
  IOutputMetadataSource, IMetadataSource, IMetadataStream {

  /**
   * @var IMetadataStream
   */
  protected $stream = null;

  public function setInputMetadataStream(IMetadataStream $stream) {
    $this -> stream = $stream;
  }

  public function clearInputMetadataStream() {
    $this -> stream = null;
  }

  public function getOutputMetadataStream(): IMetadataStream {
    return $this;
  }

  public function getOutputMetadataSource(): IMetadataSource {
    return $this;
  }

  public function readMetadata(): string {
    return $this -> stream ? $this -> stream -> readMetadata() : '\0'; // zero-length metadata
  }

  public function getMetadata(): array {
    static $pattern = "#StreamTitle='([^']*)'#iD";
    $result = [];
    if ($this -> stream) {
      $bytes = $this -> stream -> readMetadata();
      if (preg_match($pattern, $bytes, $matches)) {
        $result = parse_metadata_string(@$matches[1]);
      }
    }
    return $result;
  }

  public function getArtist(): string {
    $metadata = $this -> getMetadata();
    return $metadata['artist'];
  }

  public function getTitle(): string {
    $metadata = $this -> getMetadata();
    return $metadata['title'];
  }

  public function getDuration(): int {
    $metadata = $this -> getMetadata();
    return $metadata['duration'];
  }

}
