<?php
namespace Behat\PhantomJsExtension\Service;

use WebDriver\Service\CurlService as WebDriverCurlService;
use Symfony\Component\Console\Formatter\OutputFormatter;

class CurlService extends WebDriverCurlService {
  /**
   * @var int
   */
  protected $timeout = 120;

  /**
   * @var int
   */
  protected $connectTimeout = 120;

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

    $result = parent::execute($requestMethod, $url, $parameters, $extraOptions);

    // Output message to indicate that the page request reached the timeout.
    if ((int) $result[1]['total_time'] >= $this->timeout) {
      $formatter = new OutputFormatter(true);
      echo $formatter->format("\n<comment>Timed out after " . $this->timeout . " seconds\nURL: " . $url . "\nParameters:\n" . print_r($parameters, TRUE) . "</comment>\n");
      ob_flush();
    }

    return $result;
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
