<?php

namespace Drupal\Tests\logs_http\Unit;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LogMessageParserInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\logs_http\Logger\LogsHttpLogger;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Logs Http logger service.
 *
 * @group logs_http
 * @coversDefaultClass \Drupal\logs_http\Logger\LogsHttpLogger
 */
class LogsHttpLoggerTest extends UnitTestCase {

  /**
   * The config object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $config;

  /**
   * The log message parser service.
   *
   * @var \Drupal\Core\Logger\LogMessageParserInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $logMessageParser;

  /**
   * The severity levels array.
   *
   * @var array
   */
  protected $severityLevels;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->config = $this->prophesize(ConfigFactoryInterface::class);
    $this->logMessageParser = $this->prophesize(LogMessageParserInterface::class);
    $this->severityLevels = RfcLogLevel::getLevels();

    $this
      ->config
      ->get('logs_http.settings')
      ->willReturn($this->config->reveal());
  }

  /**
   * Tests isEnabled method.
   *
   * @covers ::isEnabled
   * @dataProvider isEnabledProvider
   */
  public function testIsEnabled($enabled, $url, $expected) {
    $this
      ->config
      ->get('enabled')
      ->willReturn($enabled);

    $this
      ->config
      ->get('url')
      ->willReturn($url);

    $logger = new LogsHttpLogger($this->config->reveal(), $this->logMessageParser->reveal());
    $result = $logger->isEnabled();

    $this->assertEquals($expected, $result);
  }

  /**
   * Provides test data to test isEnabled.
   *
   * In order to isEnabled method to return TRUE, the 'enabled' configuration
   * must be TRUE and the url must not be empty.
   *
   * @return array
   *   Array with:
   *   - "enabled" boolean value.
   *   - "url" string value.
   *   - The expected result.
   */
  public function isEnabledProvider() {
    return [
      [FALSE, '', FALSE],
      [FALSE, 'https://example.com', FALSE],
      [TRUE, '', FALSE],
      [TRUE, 'https://example.com', TRUE],
    ];
  }

}
