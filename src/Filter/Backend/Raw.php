<?php

namespace Shoutcast\Filter\Backend;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputDataStream;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMediaSource;

class Raw extends Filter implements IInputMediaSource, IOutputDataStream, IDataStream {

  /**
   * @var IMediaSource
   */
  protected $source = null;

  protected $handle = null;

  protected function openStream() {
    $this -> closeStream();

    $media = $this -> source ? $this -> source -> getNextMedia() : null;
    if (is_array($media) && isset($media['filename'])) {
      $handle = fopen($media['filename'], 'rb', false);
      if (is_resource($handle)) $this -> handle = $handle;
    }

    return $this -> handle;
  }

  protected function closeStream() {
    if ($this -> handle) fclose($this -> handle);
    $this -> handle = null;
  }

  public function __destruct() {
    $this -> closeStream();
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> source = $source;
  }

  public function clearInputMediaSource() {
    $this -> source = null;
  }

  public function getOutputDataStream(): IDataStream {
    return $this;
  }

  public function readData(int $limit): ?string {
    $handle = $this -> handle ? $this -> handle : $this -> openStream();
    if (!$handle) return null;
    $result = fread($handle, $limit);
    if (feof($handle)) $this -> closeStream();
    return $result;
  }

}
