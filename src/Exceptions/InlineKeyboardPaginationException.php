<?php

namespace TelegramBot\InlineKeyboardPagination\Exceptions;

use Exception;

/**
 * Class InlineKeyboardPaginationException
 *
 * @package TelegramBot\InlineKeyboardPagination
 */
final class InlineKeyboardPaginationException extends Exception
{
    public static function invalidMaxButtons(): self
    {
        return new self('Invalid max buttons, must be between 5 and 8.');
    }

    public static function pageMustBeBetween(int $minPage, int $maxPage): self
    {
        return new self("Invalid page selected, must be between {$minPage} and {$maxPage}.");
    }

    public static function invalidItemsPerPage(): self
    {
        return new self('Invalid number of items per page, must be at least 1.');
    }

    public static function noItems(): self
    {
        return new self('Items list empty.');
    }
}
