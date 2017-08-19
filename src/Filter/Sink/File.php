<?php

namespace Shoutcast\Filter\Sink;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputDataStream;
use Shoutcast\Interfaces\Connector\IInputMultiplexedStream;
use Shoutcast\Interfaces\IDataSink;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMultiplexedStream;

class File extends Filter implements IInputDataStream, IInputMultiplexedStream, IDataSink {

  /**
   * @var IDataStream
   */
  protected $stream = null;
  protected $filename = null;

  protected function afterConstruction() {
    $this -> filename = realpath($this -> config['filename']);
  }

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
      $data = $this -> stream -> readData($limit);
      if (($data !== null) && ($this -> filename)) {
        file_put_contents($this -> filename, $data, FILE_APPEND);
        return true;
      }
    }
    return false;
  }

}
