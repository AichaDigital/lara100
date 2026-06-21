<?php

declare(strict_types=1);

namespace AichaDigital\Lara100\Exceptions;

use Throwable;

/**
 * Marker interface for every exception thrown by lara100.
 *
 * Extends Throwable so consumers can `catch (Lara100Exception $e)` and trap the
 * whole package's failure surface without coupling to the arithmetic engine.
 */
interface Lara100Exception extends Throwable {}
