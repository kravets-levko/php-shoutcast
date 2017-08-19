<?php

namespace Shoutcast\Filter;

use Shoutcast\Interfaces\Connector\IInputDataStream;
use Shoutcast\Interfaces\Connector\IInputMetadataStream;
use Shoutcast\Interfaces\Connector\IOutputDataStream;
use Shoutcast\Interfaces\Connector\IOutputMultiplexedStream;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMetadataStream;
use Shoutcast\Interfaces\Stream\IMultiplexedStream;

class Multiplex implements IInputDataStream, IInputMetadataStream,
  IOutputDataStream, IOutputMultiplexedStream, IDataStream, IMultiplexedStream {

  /**
   * @var IDataStream
   */
  protected $data = null;
  /**
   * @var IMetadataStream
   */
  protected $metadata = null;

  protected $metaint = 0;
  protected $bytesRead = 0;
  protected $buffer = '';

  public function __construct(array $config = []) {
    $this -> metaint = @$config['metaint'];
    if ($this -> metaint < 0) $this -> metaint = 0;
  }

  public function setInputDataStream(IDataStream $stream) {
    $this -> data = $stream;
  }

  public function clearInputDataStream() {
    $this -> data = null;
  }

  public function setInputMetadataStream(IMetadataStream $stream) {
    $this -> metadata = $stream;
  }

  public function clearInputMetadataStream() {
    $this -> metadata = null;
  }

  public function getOutputDataStream(): IDataStream {
    return $this;
  }

  public function getOutputMultiplexedStream(): IMultiplexedStream {
    return $this;
  }

  protected function getMetadata(): string {
    $result = '';
    if ($this -> metadata) {
      $result = $this -> metadata -> readMetadata();
    }
    return $result != '' ? $result : '\0';
  }

  protected function readWithMetadata(int $limit): ?string {
    if ($this -> buffer == '') {
      $buffer = $this -> data ? $this -> data -> readData($limit) : null;
      if ($buffer === null) return null;
      $totalRead = $this -> bytesRead + strlen($buffer);
      if ($totalRead >= $this -> metaint) {
        // Inject metadata
        $metadata = $this -> getMetadata();

        $chunks = [];
        $chunkSize = $this -> metaint - $this -> bytesRead;
        while (strlen($buffer) >= $chunkSize) {
          $chunks[] = substr($buffer, 0, $chunkSize);
          $chunks[] = $metadata;
          $buffer = substr($buffer, $chunkSize);
          $chunkSize = $this -> metaint;
        }
        $chunks[] = $buffer;

        $this -> buffer = implode('', $chunks);
        $this -> bytesRead = $totalRead % $this -> metaint;
      } else {
        // Just append data to buffer
        $this -> bytesRead = $totalRead;
        $this -> buffer = $buffer;
      }
    }
    $result = substr($this -> buffer, 0, $limit);
    $this -> buffer = substr($this -> buffer, strlen($result));
    return $result;
  }

  protected function readWithoutMetadata(int $limit): ?string {
    if ($this -> data) {
      return $this -> data -> readData($limit);
    }
    return null;
  }

  public function readData(int $limit): ?string {
    if ($this -> metaint > 0) {
      return $this -> readWithMetadata($limit);
    } else {
      return $this -> readWithoutMetadata($limit);
    }
  }

}
