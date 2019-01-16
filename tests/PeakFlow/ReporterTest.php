<?php

use PHPUnit\Framework\TestCase;

class PeakFlowReporterTest extends TestCase {
  function testReporting() {
    $reporter = new PeakFlow\Reporter(array(
      "authToken" => "TEST_AUTH_TOKEN",
      "testing" => true
    ));

    try {
      throw new RuntimeException("Test");
    } catch(Exception $e) {
      $reporter->reportException($e);
    }

    $reports = $reporter->getReports();

    $this->assertEquals(count($reports), 1);
    $this->assertEquals($reports[0]["auth_token"], "TEST_AUTH_TOKEN");
    $this->assertEquals($reports[0]["error"]["error_class"], "RuntimeException");
    $this->assertEquals($reports[0]["error"]["message"], "Test");
  }
}
