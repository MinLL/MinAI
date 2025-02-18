<?php

function assertTrue($expression, $message = "Failed assertion") {
  if (!$expression) {
    throw new Exception($message);
  }
}

function assertFalse($expression, $message = "Failed assertion") {
  if ($expression) {
    throw new Exception($message);
  }
}

function assertString($expected, $actual, $message = "Failed assertion") {
  if ($expected != $actual) {
    print_r("\nExpected: $expected");
    print_r("\nActual  : $actual");
    print("\n");
    throw new Exception($message);
  }
}

function assertObjectAsJson($expected, $actual, $message = "Failed assertion") {
  $expectedStr = json_encode($expected);
  $actualStr = json_encode($actual);
  if ($expectedStr != $actualStr) {
    print_r("\nExpected: $expectedStr");
    print_r("\nActual  : $actualStr");
    print("\n");
    throw new Exception($message);
  }
}

