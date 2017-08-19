<?php

namespace Shoutcast\Filter\Playlist;

use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Stream\IMediaSource;

class Shuffle extends Blank implements IInputMediaSource, IOutputMediaSource, IMediaSource {

  /**
   * @var IMediaSource
   */
  protected $source = null;

  public function getPlaylist(): array {
    if ($this -> source) {
      // Update items from upstream
      $this -> items = $this -> source -> getPlaylist();
      $this -> current = -1;
    }
    shuffle($this -> items);
    return $this -> items;
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> source = $source;
    $this -> items = $source -> getPlaylist();
    shuffle($this -> items);
    $this -> current = -1;
  }

  public function clearInputMediaSource() {
    $this -> source = null;
    $this -> items = [];
    $this -> current = -1;
  }

}
