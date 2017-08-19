<?php

namespace Shoutcast\Filter\Backend;

use Shoutcast\Filter;
use Shoutcast\Interfaces\Connector\IInputMediaSource;
use Shoutcast\Interfaces\Connector\IOutputDataStream;
use Shoutcast\Interfaces\Stream\IDataStream;
use Shoutcast\Interfaces\Stream\IMediaSource;

class External extends Filter implements IInputMediaSource, IOutputDataStream, IDataStream {

  const STDIN = 0;
  const STDOUT = 1;
  const STDERR = 2;

  /**
   * @var IMediaSource
   */
  protected $playlist = null;

  protected $process = null;
  protected $pipes = [];

  protected function getPipe($pipe) {
    return array_key_exists($pipe, $this -> pipes) ? $this -> pipes[$pipe] : null;
  }

  protected function closePipe($pipe) {
    if (array_key_exists($pipe, $this -> pipes)) {
      if ($this -> pipes[$pipe]) {
        fclose($this -> pipes[$pipe]);
      }
      $this -> pipes[$pipe] = null;
    }
  }

  protected function getCommand(): string {
    return $this -> config['command'];
  }

  protected function getPlaceholders(): array {
    $result = [];

    // Get currently played file name
    if ($this -> playlist) {
      $media = $this -> playlist -> getCurrentMedia();
      if ($media && isset($media['filename'])) {
        $result['filename'] = $media['filename'];
      }
    }

    // Get some params of station
    $result = array_merge($result, array_intersect_key(
      $this -> config,
      array_flip(['format', 'bitrate', 'samplerate', 'channels'])
    ));

    return $result;
  }

  protected function processStarted() {
    $this -> closePipe(self::STDIN);
    $this -> closePipe(self::STDERR);
  }

  protected function processTerminated() {
  }

  protected function startProcess(string $command, array $placeholders = []) {
    // If previous process is running - terminate it
    $this -> terminateProcess();

    // Prepare command line
    static $pattern = '#^[a-z0-9-_]*$#iD';
    $placeholderNames = array_map(function($str) {
      return '{{' . $str . '}}';
    }, array_keys($placeholders));
    $placeholderValues = array_map(function($str) use ($pattern) {
      if (preg_match($pattern, $str)) return $str;
      return escapeshellarg($str);
    }, array_values($placeholders));
    $command = str_replace($placeholderNames, $placeholderValues, $command);

    // Start process
    $descriptor = array(
      0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
      1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
      2 => array('pipe', 'w'),  // stderr is a pipe that the child will write to
    );
    $options = array(
      'bypass_shell' => true,
    );

    $this -> process = proc_open($command, $descriptor, $this -> pipes, null, null, $options);
    if ($this -> process) {
      foreach ($this -> pipes as $pipe) {
        stream_set_blocking($pipe, 0);
      }
      $this -> processStarted();
    } else {
      $this -> pipes = [];
      $this -> process = null;
    }
  }

  protected function terminateProcess() {
    $anyPipeWasAlive = false;
    foreach ($this -> pipes as $key => $pipe) {
      if ($pipe) {
        $anyPipeWasAlive = true;
        fclose($pipe);
      }
      $this -> pipes[$key] = null;
    }
    if ($this -> process) {
      proc_terminate($this -> process);
      proc_close($this -> process);
      $this -> process = null;
      if ($anyPipeWasAlive) {
        $this -> processTerminated();
      }
    }
  }

  protected function isProcessAlive() {
    if ($this -> process) {
      $status = proc_get_status($this -> process);
      if (!$status['running']) {
        $this -> terminateProcess();
      }
    }
    return (bool)$this -> process;
  }

  protected function navigateToNextMedia() {
    if ($this -> isProcessAlive()) return true;

    if ($this -> playlist) {
      if ($this -> playlist -> getNextMedia()) {
        $this -> startProcess($this -> getCommand(), $this -> getPlaceholders());
      }
    }
    return $this -> isProcessAlive();
  }

  public function __destruct() {
    $this -> terminateProcess();
  }

  public function setInputMediaSource(IMediaSource $source) {
    $this -> playlist = $source;
  }

  public function clearInputMediaSource() {
    $this -> playlist = null;
  }

  public function getOutputDataStream(): IDataStream {
    return $this;
  }

  public function readData(int $limit): ?string {
    if ($this -> navigateToNextMedia()) {
      $pipe = $this -> getPipe(self::STDOUT);
      if ($pipe) {
        $result = fread($pipe, $limit);
        return is_string($result) ? $result : '';
      }
    }
    return null;
  }

}
