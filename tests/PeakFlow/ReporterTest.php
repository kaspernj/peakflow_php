<?php

use PHPUnit\Framework\TestCase;

class PeakFlowReporterTest extends TestCase {
  function testReporting() {
    $reporter = new PeakFlow\Reporter(array("authToken" => "TEST_AUTH_TOKEN"));

    try {
      throw new RuntimeException("Test");
    } catch(Exception $e) {
      $reporter->reportException($e);
    }
  }
}
