# Peak Flow PHP

PeakFlow error reporting for PHP.

## Installation

Add something like this to your `composer.json`:

```json
{
  "require": {
    "kaspernj/peak_flow_php": "1.0.2"
  }
}
```

## Laravel

In `app/Exceptions/Handler.php` add something like this in the `report`-method:

```php
class Handler extends ExceptionHandler {
  public function report(Exception $exception) {
    $reporter = new \PeakFlow\Reporter(array("authToken" => "your-token"));
    $reporter->reportException($exception);

    parent::report($exception);
  }
}
```
