<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A pre-order could not be reserved (switched off, or the limit is used up).
 * The message is written for the customer and is safe to show as-is.
 */
class PreOrderException extends RuntimeException
{
}
