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

  function getErrorArray($exception) {
    $result = array(
      "backtrace" => explode("\n", $exception->getTraceAsString()),
      "environment" => $_SERVER,
      "error_class" => get_class($exception),
      "file_path" => $exception->getFile(),
      "line_number" => $exception->getLine(),
      "http_method" => $this->getRequestMethod(),
      "message" => $exception->getMessage(),
      "remote_ip" => $this->getRemoteIP(),
      "url" => $this->getUrl(),
      "user_agent" => $this->getUserAgent()
    );

    if (count($_GET) > 0 || count($_POST) > 0) {
      $result["parameters"] = array_merge($_GET, $_POST);
    }

    return $result;
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

  function reportError($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) { return false;}

    try {
      switch($err_severity) {
        case E_ERROR: throw new Error ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_WARNING: throw new WarningException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_PARSE: throw new ParseException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_NOTICE: throw new NoticeException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_CORE_ERROR: throw new CoreErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_CORE_WARNING: throw new CoreWarningException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_COMPILE_ERROR: throw new CompileErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_COMPILE_WARNING: throw new CoreWarningException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_ERROR: throw new UserErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_WARNING: throw new UserWarningException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_NOTICE: throw new UserNoticeException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_STRICT: throw new StrictException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_RECOVERABLE_ERROR: throw new RecoverableErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_DEPRECATED: throw new DeprecatedException ($err_msg, 0, $err_severity, $err_file, $err_line);
        case E_USER_DEPRECATED: throw new UserDeprecatedException ($err_msg, 0, $err_severity, $err_file, $err_line);
      }
    } catch (Exception $exception) {
      $this->reportException($exception);
    }
  }

  function reportException($exception) {
    $this->report(array(
      "auth_token" => $this->getAuthToken(),
      "error" => $this->getErrorArray($exception)
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
        throw new \Exception("Couldn't report the error");
      }
    }
  }
}

class CustomError extends \Exception {
  function __construct($err_msg, $error_code, $err_severity, $err_file, $err_line) {
    parent::__construct($err_msg);
  }
}

class Error extends CustomError {}
class WarningException extends CustomError {}
class ParseException extends CustomError {}
class NoticeException extends CustomError {}
class CoreErrorException extends CustomError {}
class CoreWarningException extends CustomError {}
class CompileErrorException extends CustomError {}
class CompileWarningException extends CustomError {}
class UserErrorException extends CustomError {}
class UserWarningException extends CustomError {}
class UserNoticeException extends CustomError {}
class StrictException extends CustomError {}
class RecoverableErrorException extends CustomError {}
class DeprecatedException extends CustomError {}
class UserDeprecatedException extends CustomError {}
