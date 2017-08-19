<?php

namespace Shoutcast\Filter;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputMetadataSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataStream;
use Shoutcast\Interfaces\Stream\IMetadataSource;
use Shoutcast\Interfaces\Stream\IMetadataStream;

class MetadataStream extends Filter implements IInputMetadataSource,
  IOutputMetadataStream, IOutputMetadataSource,
  IMetadataSource, IMetadataStream {

  protected $sources = [];
  protected $metadata = null;
  protected $bytes = null;

  public function setInputMetadataSource(IMetadataSource $source) {
    $this -> sources = [$source];
    $this -> metadata = null;
    $this -> bytes = null;
  }

  public function addInputMetadataSource(IMetadataSource $source) {
    if (!in_array($source, $this -> sources)) {
      $this -> sources[] = $source;
      $this -> metadata = null;
      $this -> bytes = null;
    }
  }

  public function removeInputMetadataSource(IMetadataSource $source) {
    $index = array_search($source, $this -> sources);
    if ($index !== false) {
      array_splice($this -> sources, $index, 1);
      $this -> metadata = null;
      $this -> bytes = null;
    }
  }

  public function clearInputMetadataSources() {
    $this -> sources = [];
    $this -> metadata = null;
    $this -> bytes = null;
  }

  public function getOutputMetadataStream(): IMetadataStream {
    return $this;
  }

  public function getOutputMetadataSource(): IMetadataSource {
    return $this;
  }

  public function getMetadata(): array {
    $metadata = [];
    /**
     * @var IMetadataSource $source
     */
    foreach ($this -> sources as $source) {
      $metadata = array_merge($metadata, $source -> getMetadata());
    }
    if ($metadata != $this -> metadata) {
      $this -> metadata = $metadata;
      $this -> bytes = null;
    }
    return $this -> metadata;
  }

  public function getArtist(): string {
    $metadata = $this -> getMetadata();
    return isset($metadata['artist']) ? $metadata['artist'] : '';
  }

  public function getTitle(): string {
    $metadata = $this -> getMetadata();
    return isset($metadata['title']) ? $metadata['title'] : '';
  }

  public function getDuration(): int {
    $metadata = $this -> getMetadata();
    return isset($metadata['duration']) ? $metadata['duration'] : -1;
  }

  public function readMetadata(): string {
    $this -> getMetadata(); // invalidate and update internal state
    if ($this -> bytes === null) {
      static $keys = ['artist' => true, 'title' => true];

      $metadata = array_intersect_key($this -> getMetadata(), $keys);
      $metadata = array_map('trim', $metadata);
      $metadata = array_filter($metadata, 'strlen');
      $metadata = implode(' - ', $metadata);

      $metadata = trim(str_replace("'", '`', $metadata));
      $metadata = substr($metadata, 0, 16 * 255 - strlen("StreamTitle='';"));

      if ($metadata != '') {
        $bytes = "StreamTitle='{$metadata}';";
        $n = strlen($bytes);
        if ($n % 16 != 0) {
          $n = 16 * (int)(($n + 15) / 16);
          $bytes = str_pad($bytes, $n, "\0", STR_PAD_RIGHT);
        }
        $this -> bytes = chr((int)($n / 16)) . $bytes;
      } else {
        $this -> bytes = "\0"; // zero-length data
      }
    }
    return $this -> bytes;
  }

}
