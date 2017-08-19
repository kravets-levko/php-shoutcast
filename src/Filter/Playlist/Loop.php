<?php

namespace Shoutcast\Filter\Playlist;

use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputMediaSource;
use Shoutcast\Interfaces\Stream\IMediaSource;

class Loop extends Blank implements IInputMediaSource, IOutputMediaSource, IMediaSource {

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
    return $this -> items;
  }

  protected function getNextItemIndex(int $currentIndex, array $items = []): int {
    $result = $currentIndex + 1;
    if ($result >= count($items)) {
      if ($this -> source) {
        $this -> items = $this -> source -> getPlaylist();
        $items = $this -> items;
      }
      $result = count($items) > 0 ? 0 : -1;
    }
    return $result;
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> source = $source;
    $this -> items = $source -> getPlaylist();
    $this -> current = -1;
  }

  public function clearInputMediaSource() {
    $this -> source = null;
    $this -> items = [];
    $this -> current = -1;
  }

}
