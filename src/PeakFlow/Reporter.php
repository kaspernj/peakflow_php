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

  function getRemoteIP() {
    if (array_key_exists("HTTP_X_FORWARDED_FOR", $_SERVER) && $_SERVER["HTTP_X_FORWARDED_FOR"]) {
      return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if (array_key_exists("REMOTE_ADDR", $_SERVER) && $_SERVER["REMOTE_ADDR"]) {
      return $_SERVER["REMOTE_ADDR"];
    }
  }

  function getReports() {
    return $this->reports;
  }

  function getRequestMethod() {
    if (array_key_exists("REQUEST_METHOD", $_SERVER)) {
      return $_SERVER["REQUEST_METHOD"];
    }
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
        "backtrace" => explode("\n", $exception->getTraceAsString()),
        "environment" => $_SERVER,
        "error_class" => get_class($exception),
        "file_path" => $exception->getFile(),
        "http_method" => $this->getRequestMethod(),
        "message" => $exception->getMessage(),
        "remote_ip" => $this->getRemoteIP(),
        "parameters" => array_merge($_GET, $_POST),
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
          "header" => "Content-Type: application/json\r\n",
          "method" => "POST",
          "content" => json_encode($data)
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
