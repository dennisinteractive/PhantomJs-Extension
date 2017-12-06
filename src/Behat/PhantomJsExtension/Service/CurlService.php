<?php
namespace Behat\PhantomJsExtension\Service;

use WebDriver\Service\CurlService as WebDriverCurlService;

class CurlService extends WebDriverCurlService {
  /**
   * @var int
   */
  protected $timeout = 60;

  /**
   * @var int
   */
  protected $connectTimeout = 60;

  /**
   * {@inheritdoc}
   */
  public function execute($requestMethod, $url, $parameters = NULL, $extraOptions = NULL) {
    $extraOptions = array_replace(
      $extraOptions,
      array(
        CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
        CURLOPT_TIMEOUT => $this->timeout,
      )
    );
    return parent::execute($requestMethod, $url, $parameters, $extraOptions);
  }

  /**
   * Set timeout.
   *
   * @param int $seconds
   */
  public function setTimeout($seconds) {
    $this->timeout = (int) $seconds;
  }

  /**
   * Set connect timeout.
   *
   * @param int $seconds
   */
  public function setConnectTimeout($seconds) {
    $this->connectTimeout = (int) $seconds;
  }
}
