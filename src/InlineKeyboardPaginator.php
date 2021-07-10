<?php

namespace TelegramBot\InlineKeyboardPagination;

/**
 * Interface InlineKeyboardPaginator
 *
 * @package TelegramBot\InlineKeyboardPagination
 */
interface InlineKeyboardPaginator
{
    /**
     * InlineKeyboardPaginator constructor.
     *
     * @param array  $items
     * @param string $command
     * @param int    $selectedPage
     * @param int    $itemsPerPage
     */
    public function __construct(array $items, string $command, int $selectedPage, int $itemsPerPage);

    /**
     * Set the maximum number of keyboard buttons to show.
     *
     * @param int  $maxButtons
     * @param bool $forceButtonCount
     *
     * @return self
     */
    public function setMaxButtons(int $maxButtons = 5, bool $forceButtonCount = false): self;

    /**
     * Set command for this pagination.
     *
     * @param string $command
     *
     * @return self
     */
    public function setCommand(string $command = 'pagination'): self;

    /**
     * Set the selected page.
     *
     * @param int $selectedPage
     *
     * @return self
     */
    public function setSelectedPage(int $selectedPage): self;

    /**
     * Get the pagination data for the passed page.
     *
     * @param int|null $selectedPage
     *
     * @return array
     */
    public function getPagination(?int $selectedPage = null): array;
}
