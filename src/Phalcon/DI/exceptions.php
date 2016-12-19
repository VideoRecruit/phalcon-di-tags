<?php

namespace VideoRecruit\Phalcon\DI;

/**
 * Common exception interface.
 */
interface Exception
{
}

/**
 * Class InvalidArgumentException
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}
