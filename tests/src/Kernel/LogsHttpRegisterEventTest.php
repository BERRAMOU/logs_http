<?php

namespace Drupal\Tests\logs_http\Kernel;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test registration of an event.
 *
 * @group logs_http
 */
class LogsHttpRegisterEventTest extends KernelTestBase {

  /**
   * The config for Logs http.
   *
   * @var \Drupal\Core\Config\Config
   */
  private $logsHttpConfig;

  /**
   * The Logs http service.
   *
   * @var \Drupal\logs_http\Logger\LogsHttpLoggerInterface
   */
  private $logsHttpLogger;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['logs_http'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Installing needed schema.
    $this->installConfig(static::$modules);

    // Setup the configuration.
    $this->logsHttpConfig = \Drupal::configFactory()->getEditable('logs_http.settings');
    $this->logsHttpConfig->set('url', 'http://www.example.com');
    $this->logsHttpConfig->save();

    $this->logsHttpLogger = \Drupal::service('logs_http.logs_http_logger');
  }

  /**
   * Test registration of an event.
   */
  public function testRegisterEvent() {
    // Test severity.
    \Drupal::logger('logs_http')->notice('Notice 1');
    $events = $this->logsHttpLogger->getEvents();
    $this->assertEmpty($events, 'No notice events registered, as severity level was too high.');

    // Set severity.
    $this->logsHttpConfig->set('severity_level', RfcLogLevel::NOTICE);
    $this->logsHttpConfig->save();

    // Test single event.
    $this->logsHttpLogger->reset();
    \Drupal::logger('logs_http')->error('Notice 1');
    $events = $this->logsHttpLogger->getEvents();
    $this->assertEquals(1, count($events), 'Notice events registered.');

    // Test multiple events.
    $this->logsHttpLogger->reset();

    // A duplicated event.
    \Drupal::logger('logs_http')->notice('Notice 1');
    \Drupal::logger('logs_http')->notice('Notice 1');

    \Drupal::logger('logs_http')->notice('Notice 2');
    $events = $this->logsHttpLogger->getEvents();
    $this->assertEquals(2, count($events), 'Correct number of events registered.');

    // Get the elements.
    $event1 = array_shift($events);
    $event2 = array_shift($events);

    $this->assertEquals('Notice 1', $event1['message'], 'Correct first event registered.');
    $this->assertEquals('Notice 2', $event2['message'], 'Correct second event registered.');
  }

}
