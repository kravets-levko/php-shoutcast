<?php

namespace Shoutcast\Filter\Metadata;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMetadataSource;
use Shoutcast\Interfaces\Stream\IMediaSource;
use Shoutcast\Interfaces\Stream\IMetadataSource;

class FileName extends Filter implements IInputMediaSource,
  IOutputMetadataSource, IMetadataSource {

  /**
   * @var IMediaSource
   */
  protected $source = null;
  protected $media = null;
  protected $metadata = [];

  protected function updateMetadata() {
    $this -> metadata = [];
    if ($this -> media) {
      $path = '';
      if (isset($this -> media['filename'])) {
        $path = $this -> media['filename'];
      } elseif (isset($this -> media['url'])) {
        $path = parse_url($this -> media['url'], PHP_URL_PATH);
      }
      if (is_string($path) && ($path != '')) {
        $path = explode('/', str_replace('\\', '/', $path));
        $info = pathinfo(array_pop($path), PATHINFO_FILENAME);
        $this -> metadata = parse_metadata_string($info);
      }
    }
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> source = $source;
    $this -> media = null;
    $this -> updateMetadata();
  }

  public function clearInputMediaSource() {
    $this -> source = null;
    $this -> media = null;
    $this -> updateMetadata();
  }

  public function getOutputMetadataSource(): IMetadataSource {
    return $this;
  }

  public function isDirty(): bool {
    $media = $this -> source ? $this -> source -> getCurrentMedia() : null;
    if ($media != $this -> media) {
      $this -> media = $media;
      $this -> updateMetadata();
      return true;
    } else {
      return false;
    }
  }

  public function getMetadata(): array {
    $media = $this -> source ? $this -> source -> getCurrentMedia() : null;
    if ($media != $this -> media) {
      $this -> media = $media;
      $this -> updateMetadata();
    }
    return $this -> metadata;
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
