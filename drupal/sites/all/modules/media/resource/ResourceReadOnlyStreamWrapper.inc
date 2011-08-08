<?php

/**
 * A base class for Resource Stream Wrappers.
 *
 * This class provides a complete stream wrapper implementation. It passes
 * incoming  URL's through an interpolation method then recursively calls
 * the invoking PHP filesystem function.
 *
 * DrupalStreamWrapper implementations need to override at least the interpolateUrl
 * method to rewrite the URL before is it passed back into the calling function.
 *
 */
abstract class ResourceReadOnlyStreamWrapper implements ResourceStreamWrapperInterface {
  private $parameters = array();
  private $base_url = NULL;

  function interpolateUrl($url) {
    return $this->base_url .'?'. implode('&', $this->parameters);
  }

  function htmlUrl($url) {
    return $this->interpolateUrl($url);
  }

  function mime($url) {
    return 'application/octet-stream';
  }

  /**
   *  Report an error.
   *  @param $message
   *    The untranslated string to report.
   *  @param $options
   *    An optional array of options to send to t().
   *  @param $display
   *    If TRUE, then we display the error to the user.
   *  @return
   *    We return FALSE, since we sometimes pass that back from the reporting
   *    function.
   */
  private function _report_error($message, $options = array(), $display = FALSE) {
    watchdog('resource', $message, $options, WATCHDOG_ERROR);
    if ($display) {
      drupal_set_message(t($message, $options), 'error');
    }
    return FALSE;
  }

  /**
   *  Returns an array of any parameters stored in the URL's path.
   *  @param $url
   *    The URL to parse, such as youtube://v/[video-code]/t/[tags+more-tags].
   *  @return
   *    An associative array of all the parameters in the path,
   *    or FALSE if the $url is ill-formed.
   */
  private function _parse_url($url) {
    $path = parse_url($url, PHP_URL_PATH);
    if ($path === FALSE) {
      // Non-recoverable error when parsing the path.
      return FALSE;
    }
    $parts = explode('/', $path);
    $params = array();
    $count = 0;
    $total = count($parts);
    if (!$total || ($total % 2)) {
      // If we have no parts, or an odd number of parts, it's malformed.
      return FALSE;
    }
    while ($count < $total) {
      // We iterate count for each step of the assignment to keep us honest.
      $params[$parts[$count++]] = $parts[$count++];
    }
    return $params;
  }

  /**
   * Support for fopen(), file_get_contents(), file_put_contents() etc.
   *
   * @param $path
   *   A string containing the path to the file to open.
   * @param $mode
   *   The file mode ("r", "wb" etc.).
   * @param $options
   *   A bit mask of STREAM_USE_PATH and STREAM_REPORT_ERRORS.
   * @param &$opened_path
   *   A string containing the path actually opened.
   * @return
   *  TRUE if file was opened successfully.
   */
  public function stream_open($url, $mode, $options, &$opened_url) {
    resource_debug(t('Stream open: %url', array('%url' => $url)));

    // We only handle Read-Only mode by default.
    if ($mode != 'r') {
      return $this->_report_error('Attempted to open %url as mode: @mode.', array('%url' => $url, '%mode' => $mode), ($options & STREAM_REPORT_ERRORS));
    }

    // We parse a URL as youtube://v/dsyiufo34/t/cats+dogs to store
    // the relevant code(s) in our private array of parameters.
    $this->parameters = $this->_parse_url($url);

    if ($this->parameters === FALSE) {
      return $this->_report_error('Attempted to parse an ill-formed url: %url.', array('%url' => $url), ($options & STREAM_REPORT_ERRORS));
    }

    if ((bool)$this->parameters && ($options & STREAM_USE_PATH)) {
      $opened_url = $url;
    }

    resource_debug(t('Stream opened: %parameters', array('%parameters' => print_r($this->parameters, TRUE))));

    return (bool)$this->parameters;
  }

  // Undocumented PHP stream wrapper method.
  function stream_lock($operation) {
    return FALSE;
  }

  /**
   * Support for fread(), file_get_contents() etc.
   *
   * @param $count
   *    Maximum number of bytes to be read.
   * @return
   *  The string that was read, or FALSE in case of an error.
   */
  public function stream_read($count) {
    return FALSE;
  }

  /**
   * Support for fwrite(), file_put_contents() etc.
   *
   * @param $data
   *   The string to be written.
   * @return
   *   The number of bytes written.
   */
  public function stream_write($data) {
    return FALSE;
  }

  /**
   * Support for feof().
   *
   * @return
   *   TRUE if end-of-file has been reached.
   */
  public function stream_eof() {
    return FALSE;
  }

  /**
   * Support for fseek().
   *
   * @param $offset
   *   The byte offset to got to.
   * @param $whence
   *   SEEK_SET, SEEK_CUR, or SEEK_END.
   * @return
   *   TRUE on success
   */
  public function stream_seek($offset, $whence) {
    return FALSE;
  }

  /**
   * Support for fflush().
   *
   * @return
   *   TRUE if data was successfully stored (or there was no data to store).
   */
  public function stream_flush() {
    return FALSE;
  }

  /**
   * Support for ftell().
   *
   * @return
   *   The current offset in bytes from the beginning of file.
   */
  public function stream_tell() {
    return FALSE;
  }

  /**
   * Support for fstat().
   *
   * @return
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   */
  public function stream_stat() {
    return FALSE;
  }

  /**
   * Support for fclose().
   *
   * @return
   *   TRUE if stream was successfully closed.
   */
  public function stream_close() {
    return TRUE;
  }

  /**
   * Support for unlink().
   *
   * @param $url
   *   A string containing the url to the resource to delete.
   * @return
   *   TRUE if resource was successfully deleted.
   */
//   public function unlink($url) {
//     return unlink($this->interpolateUrl($url));
//   }

  /**
   * Support for rename().
   *
   * @param $fromUrl,
   *   The url to the file to rename.
   * @param $toUrl
   *   The new url for file.
   *
   * @return
   *   TRUE if file was successfully renamed.
   */
//   public function rename($fromUrl, $toUrl) {
//     return rename($this->interpolateUrl($fromUrl), $this->interpolateUrl($toUrl));
//   }

  /**
   * Support for mkdir().
   *
   * @param $url
   *   A string containing the url to the directory to create.
   * @param $mode
   *   Permission flags - see mkdir().
   * @param $options
   *   A bit mask of STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE.
   * @return
   *   TRUE if directory was successfully created.
   */
//   public function mkdir($url, $mode, $options) {
//     $recursive = (bool)($options & STREAM_MKDIR_RECURSIVE);
//     if ($options & STREAM_REPORT_ERRORS) {
//       return mkdir($this->interpolateUrl($url), $mode, $recursive);
//     }
//     else {
//       return @mkdir($this->interpolateUrl($url), $mode, $recursive);
//     }
//   }

  /**
   * Support for rmdir().
   *
   * @param $url
   *   A string containing the url to the directory to delete.
   * @param $options
   *   A bit mask of STREAM_REPORT_ERRORS.
   * @return
   *   TRUE if directory was successfully removed.
   */
//   public function rmdir($url, $options) {
//     if ($options & STREAM_REPORT_ERRORS) {
//       return rmdir($this->interpolateUrl($url));
//     }
//     else {
//       return @rmdir($this->interpolateUrl($url));
//     }
//   }

  /**
   * Support for stat().
   *
   * @param $url
   *   A string containing the url to get information about.
   * @param $flags
   *   A bit mask of STREAM_URL_STAT_LINK and STREAM_URL_STAT_QUIET.
   * @return
   *   An array with file status, or FALSE in case of an error - see fstat()
   *   for a description of this array.
   */
  public function url_stat($url, $flags) {
    return FALSE;
  }

  /**
   * Support for opendir().
   *
   * @param $url
   *   A string containing the url to the directory to open.
   * @param $options
   *   Unknown (parameter is not documented in PHP Manual).
   * @return
   *   TRUE on success.
   */
  public function dir_opendir($url, $options) {
    return FALSE;
  }

  /**
   * Support for readdir().
   *
   * @return
   *   The next filename, or FALSE if there are no more files in the directory.
   */
  public function dir_readdir() {
    return FALSE;
  }

  /**
   * Support for rewinddir().
   *
   * @return
   *   TRUE on success.
   */
  public function dir_rewinddir() {
    return FALSE;
  }

  /**
   * Support for closedir().
   *
   * @return
   *   TRUE on success.
   */
  public function dir_closedir() {
    return FALSE;
  }
}


