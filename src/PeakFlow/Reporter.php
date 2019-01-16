<?php

namespace PeakFlow;

class Reporter {
  function __construct($args) {
    $this->args = $args;
    $this->reports = array();
  }

  function getAuthToken() {
    return $this->args["authToken"];
  }

  function getPeakFlowUrl() {
    if (array_key_exists("peakFlowUrl", $this->args)) {
      return $this->args["peakFlowUrl"];
    } else {
      return "https://www.peakflow.io/errors/reports";
    }
  }

  function getReports() {
    return $this->reports;
  }

  function getUrl() {
    if (!array_key_exists("SERVER_PORT", $_SERVER)) {
      return null;
    }

    $url = "http";

    if ($_SERVER["SERVER_PORT"] == 443) {
      $url .= "s";
    }

    $url .= "//";
    $url .= $_SERVER["HTTP_HOST"];
    $url .= $_SERVER["REQUEST_URI"];

    return $url;
  }

  function getUserAgent() {
    if (array_key_exists("HTTP_USER_AGENT", $_SERVER)) {
      return $_SERVER["HTTP_USER_AGENT"];
    }
  }

  function isTesting() {
    if (array_key_exists("testing", $this->args) && $this->args["testing"]) {
      return true;
    }

    return false;
  }

  function reportException($exception) {
    $this->report(array(
      "auth_token" => $this->getAuthToken(),
      "error" => array(
        "backtrace" => $exception->getTraceAsString(),
        "error_class" => get_class($exception),
        "message" => $exception->getMessage(),
        "url" => $this->getUrl(),
        "user_agent" => $this->getUserAgent()
      )
    ));
  }

  function report($data) {
    if ($this->isTesting()) {
      $this->reports[] = $data;
    } else {
      $options = array(
        "http" => array(
          "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
          "method" => "POST",
          "content" => http_build_query($data)
        )
      );
      $context = stream_context_create($options);
      $result = file_get_contents($this->getPeakFlowUrl(), false, $context);

      if ($result === false) {
        throw new Exception("Couldn't report the error");
      }
    }
  }
}
