<?php

use PHPUnit\Framework\TestCase;

class PeakFlowReporterTest extends TestCase {
  function testReporting() {
    $reporter = new PeakFlow\Reporter(array(
      "authToken" => "TEST_AUTH_TOKEN",
      "peakFlowUrl" => "http://peakflow.development:18000/errors/reports"
    ));

    try {
      throw new RuntimeException("Test");
    } catch(Exception $e) {
      $reporter->reportException($e);
    }
  }
}
