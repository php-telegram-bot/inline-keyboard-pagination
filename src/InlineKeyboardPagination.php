<?php

namespace TelegramBot\InlineKeyboardPagination;

use TelegramBot\InlineKeyboardPagination\Exceptions\InlineKeyboardPaginationException;

/**
 * Class InlineKeyboardPagination
 *
 * @package TelegramBot\InlineKeyboardPagination
 */
class InlineKeyboardPagination implements InlineKeyboardPaginator
{
    private array $items;
    private int $itemsPerPage;
    private int $selectedPage;
    private int $maxButtons = 5;
    private bool $forceButtonCount = false;
    private string $command;
    private string $callbackDataFormat = 'command={COMMAND}&oldPage={OLD_PAGE}&newPage={NEW_PAGE}';
    private array $labels = [
        'default'  => '%d',
        'first'    => '« %d',
        'previous' => '‹ %d',
        'current'  => '· %d ·',
        'next'     => '%d ›',
        'last'     => '%d »',
    ];

    /**
     * @param int  $maxButtons
     * @param bool $forceButtonCount
     *
     * @return $this
     * @throws InlineKeyboardPaginationException
     */
    public function setMaxButtons(int $maxButtons = 5, bool $forceButtonCount = false): InlineKeyboardPagination
    {
        if ($maxButtons < 5 || $maxButtons > 8) {
            throw InlineKeyboardPaginationException::invalidMaxButtons();
        }

        $this->maxButtons       = $maxButtons;
        $this->forceButtonCount = $forceButtonCount;

        return $this;
    }

    /**
     * Get the current callback format.
     *
     * @return string
     */
    public function getCallbackDataFormat(): string
    {
        return $this->callbackDataFormat;
    }

    /**
     * Set the callback_data format.
     *
     * @param string $callbackDataFormat
     *
     * @return InlineKeyboardPagination
     */
    public function setCallbackDataFormat(string $callbackDataFormat): InlineKeyboardPagination
    {
        $this->callbackDataFormat = $callbackDataFormat;

        return $this;
    }

    /**
     * Return list of keyboard button labels.
     *
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Set the keyboard button labels.
     *
     * @param array $labels
     *
     * @return InlineKeyboardPagination
     */
    public function setLabels(array $labels): InlineKeyboardPagination
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCommand(string $command = 'pagination'): InlineKeyboardPagination
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @inheritdoc
     * @throws InlineKeyboardPaginationException
     */
    public function setSelectedPage(int $selectedPage): InlineKeyboardPagination
    {
        $numberOfPages = $this->getNumberOfPages();
        if ($selectedPage < 1 || $selectedPage > $numberOfPages) {
            throw InlineKeyboardPaginationException::pageMustBeBetween(1, $numberOfPages);
        }

        $this->selectedPage = $selectedPage;

        return $this;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Set how many items should be shown per page.
     *
     * @param int $itemsPerPage
     *
     * @return InlineKeyboardPagination
     * @throws InlineKeyboardPaginationException
     */
    public function setItemsPerPage(int $itemsPerPage): InlineKeyboardPagination
    {
        if ($itemsPerPage <= 0) {
            throw InlineKeyboardPaginationException::invalidItemsPerPage();
        }

        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * Set the items for the pagination.
     *
     * @param array $items
     *
     * @return InlineKeyboardPagination
     * @throws InlineKeyboardPaginationException
     */
    public function setItems(array $items): InlineKeyboardPagination
    {
        if (empty($items)) {
            throw InlineKeyboardPaginationException::noItems();
        }

        $this->items = $items;

        return $this;
    }

    /**
     * Calculate and return the number of pages.
     *
     * @return int
     */
    public function getNumberOfPages(): int
    {
        return (int) ceil(count($this->items) / $this->itemsPerPage);
    }

    /**
     * TelegramBotPagination constructor.
     *
     * @inheritdoc
     * @throws InlineKeyboardPaginationException
     */
    public function __construct(
        array $items,
        string $command = 'pagination',
        int $selectedPage = 1,
        int $itemsPerPage = 5
    ) {
        $this->setCommand($command);
        $this->setItemsPerPage($itemsPerPage);
        $this->setItems($items);
        $this->setSelectedPage($selectedPage);
    }

    /**
     * @inheritdoc
     * @throws InlineKeyboardPaginationException
     */
    public function getPagination(int $selectedPage = null): array
    {
        if ($selectedPage !== null) {
            $this->setSelectedPage($selectedPage);
        }

        return [
            'items'    => $this->getPreparedItems(),
            'keyboard' => $this->generateKeyboard(),
        ];
    }

    /**
     * Generate the keyboard with the correctly labelled buttons.
     *
     * @return array
     */
    protected function generateKeyboard(): array
    {
        $buttons = $this->generateButtons();
        $buttons = $this->applyButtonLabels($buttons);

        return array_values(array_filter($buttons));
    }

    /**
     * Generate all buttons for this inline keyboard.
     *
     * @return array
     */
    protected function generateButtons(): array
    {
        $numberOfPages = $this->getNumberOfPages();

        $range = ['from' => 2, 'to' => $numberOfPages - 1];

        if ($numberOfPages > $this->maxButtons) {
            $range = $this->generateRange();
        }

        $buttons[1] = $this->generateButton(1);
        for ($i = $range['from']; $i <= $range['to']; $i++) {
            $buttons[$i] = $this->generateButton($i);
        }
        $buttons[$numberOfPages] = $this->generateButton($numberOfPages);

        return $buttons;
    }

    /**
     * Apply correct text labels to the keyboard buttons.
     *
     * @param array $buttons
     *
     * @return array
     */
    protected function applyButtonLabels(array $buttons): array
    {
        $numberOfPages = $this->getNumberOfPages();

        foreach ($buttons as $page => &$button) {
            $inFirstBlock = max($this->selectedPage, $page) <= 3;
            $inLastBlock  = min($this->selectedPage, $page) >= $numberOfPages - 2;

            $labelKey = 'next';
            if ($page === $this->selectedPage) {
                $labelKey = 'current';
            } elseif ($inFirstBlock || $inLastBlock) {
                $labelKey = 'default';
            } elseif ($page === 1) {
                $labelKey = 'first';
            } elseif ($page === $numberOfPages) {
                $labelKey = 'last';
            } elseif ($page < $this->selectedPage) {
                $labelKey = 'previous';
            }

            $label = $this->labels[$labelKey] ?? '';

            // Remove button for undefined labels.
            if ($label === '') {
                $button = null;
                continue;
            }

            $button['text'] = sprintf($label, $page);
        }

        return $buttons;
    }

    /**
     * Get the range of intermediate buttons for the keyboard.
     *
     * @return array
     */
    protected function generateRange(): array
    {
        $numberOfIntermediateButtons = $this->maxButtons - 2; // Minus first and last buttons.
        $numberOfPages               = $this->getNumberOfPages();

        $from = $this->selectedPage - 1;
        $to   = $this->selectedPage + 1;

        if ($this->selectedPage === 1) {
            $from = 2;
            $to   = $this->maxButtons - 1;
        } elseif ($this->selectedPage === $numberOfPages) {
            $from = $numberOfPages - $numberOfIntermediateButtons;
            $to   = $numberOfPages - 1;
        } elseif ($this->selectedPage === 3) {
            // Special case because this button is in the center of a flexible pagination.
            $to += $numberOfIntermediateButtons - 3;
        } elseif ($this->selectedPage < 3) {
            // First half.
            $from = $this->selectedPage;
            $to   = $this->selectedPage + $numberOfIntermediateButtons - 1;
        } elseif (($numberOfPages - $this->selectedPage) < 3) {
            // Last half.
            $from = $numberOfPages - $numberOfIntermediateButtons;
            $to   = $numberOfPages - 1;
        } elseif ($this->forceButtonCount) {
            $from = (int) max(2, $this->selectedPage - floor($numberOfIntermediateButtons / 2));
            $to   = $from + $numberOfIntermediateButtons - 1;
        }

        return compact('from', 'to');
    }

    /**
     * Generate the button for the passed page.
     *
     * @param int $page
     *
     * @return array
     */
    protected function generateButton(int $page): array
    {
        return [
            'text'          => $page,
            'callback_data' => $this->generateCallbackData($page),
        ];
    }

    /**
     * Generate the callback data for the passed page.
     *
     * @param int $page
     *
     * @return string
     */
    protected function generateCallbackData(int $page): string
    {
        return str_replace(
            ['{COMMAND}', '{OLD_PAGE}', '{NEW_PAGE}'],
            [$this->command, $this->selectedPage, $page],
            $this->callbackDataFormat
        );
    }

    /**
     * Get the prepared items for the selected page.
     *
     * @return array
     */
    protected function getPreparedItems(): array
    {
        return array_slice($this->items, $this->getOffset(), $this->itemsPerPage);
    }

    /**
     * Get the items offset for the selected page.
     *
     * @return int
     */
    protected function getOffset(): int
    {
        return $this->itemsPerPage * ($this->selectedPage - 1);
    }

    /**
     * Get the parameters from the callback query.
     *
     * @todo Possibly make it work for custom formats too?
     *
     * @param string $data
     *
     * @return array
     */
    public static function getParametersFromCallbackData(string $data): array
    {
        parse_str($data, $params);

        return $params;
    }
}
