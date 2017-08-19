<?php

namespace Shoutcast\Filter\Sink;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputDataStream;
use Shoutcast\Interfaces\Connector\IInputMultiplexedStream;
use Shoutcast\Interfaces\IDataSink;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMultiplexedStream;

class Client extends Filter implements IInputDataStream, IInputMultiplexedStream, IDataSink {

  /**
   * @var IDataStream
   */
  protected $stream = null;

  protected $time = 0;
  protected $bytesSent = 0;
  protected $speedLimit = 0;

  protected function afterConstruction() {
    $this -> time = microtime(true);
    $this -> bytesSent = 0;
    $this -> speedLimit = (int)@$this -> config['speed_limit'];
    if ($this -> speedLimit <= 0) $this -> speedLimit = 0;
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
    if (!headers_sent()) {
      foreach ($headers as $header) {
        header($header, true);
      }
    }
  }

  public function drownData(int $limit): bool {
    if ($this -> stream) {
      $data = $this -> stream -> readData($limit);
      if ($data !== null) {
        if ($data != '') {
          echo $data;
          @ob_flush();
          flush();

          if ($this -> speedLimit) {
            $this -> bytesSent += strlen($data);
            if ($this -> bytesSent >= $this -> speedLimit) {
              // `speed_limit` is amout of bytes expected per second
              // when we reach this limit - we should calculate time delta.
              // if limit was reached less than expected time (1 second) -
              // we should sleep rest of time.
              $deltaTime = microtime(true) - $this -> time;
              $sleep = 1 - $deltaTime;
              usleep(round($sleep * 1000000)); // sleep for time depending on rate overhead
              $this -> bytesSent = 0;
              $this -> time = microtime(true);
            }
          }
        }
        return true;
      }
    }
    return false;
  }

}
