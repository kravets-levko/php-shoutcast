<?php

namespace Shoutcast\Filter\Sink;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputDataStream;
use Shoutcast\Interfaces\Connector\IInputMultiplexedStream;
use Shoutcast\Interfaces\IDataSink;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMultiplexedStream;

class Discard extends Filter implements IInputDataStream, IInputMultiplexedStream, IDataSink {

  /**
   * @var IDataStream
   */
  protected $stream = null;

  public function setInputDataStream(IDataStream $stream) {
    $this -> stream = $stream;
  }

  public function clearInputDataStream() {
    $this -> stream = null;
  }

  public function setInputMultiplexedStream(IMultiplexedStream $stream) {
    $this -> stream = $stream;
  }

  public function clearInputMultiplexedStream() {
    $this -> stream = null;
  }

  public function drownHeaders(array $headers) {
    // Do nothing
  }

  public function drownData(int $limit): bool {
    if ($this -> stream) {
      // Just read - don't output anywhere
      $data = $this -> stream -> readData($limit);
      return $data !== null;
    }
    return false;
  }

}
