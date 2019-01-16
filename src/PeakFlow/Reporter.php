<?php

namespace PeakFlow;

class Reporter {
  function __construct($args) {
    $this->authToken = $args["authToken"];
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

  function reportException($exception) {
    $this->report(array(
      "auth_token" => $this->authToken,
      "error" => array(
        "backtrace" => $exception->getTrace(),
        "error_class" => get_class($exception),
        "message" => $exception->getMessage(),
        "url" => $this->getUrl(),
        "user_agent" => $this->getUserAgent()
      )
    ));
  }

  function report($data) {
    $url = "https://www.peakflow.io/errors/reports";
    $options = array(
      "http" => array(
        "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($data)
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
      throw new Exception("Couldn't report the error");
    }
  }
}
