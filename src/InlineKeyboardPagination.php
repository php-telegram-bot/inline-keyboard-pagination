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
    /**
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var int
     */
    private $maxButtons = 5;

    /**
     * @var bool
     */
    private $forceButtonCount = false;

    /**
     * @var int
     */
    private $selectedPage;

    /**
     * @var array
     */
    private $items;

    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $callbackDataFormat = 'command={COMMAND}&oldPage={OLD_PAGE}&newPage={NEW_PAGE}';

    /**
     * @var array
     */
    private $labels = [
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
            throw new InlineKeyboardPaginationException('Invalid max buttons, must be between 5 and 8.');
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
            throw new InlineKeyboardPaginationException(
                'Invalid selected page, must be between 1 and ' . $numberOfPages
            );
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
            throw new InlineKeyboardPaginationException('Invalid number of items per page, must be at least 1');
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
            throw new InlineKeyboardPaginationException('Items list empty.');
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
        $buttons       = [];
        $numberOfPages = $this->getNumberOfPages();

        if ($numberOfPages > $this->maxButtons) {
            $buttons[1] = $this->generateButton(1);

            $range = $this->generateRange();
            for ($i = $range['from']; $i < $range['to']; $i++) {
                $buttons[$i] = $this->generateButton($i);
            }

            $buttons[$numberOfPages] = $this->generateButton($numberOfPages);
        } else {
            for ($i = 1; $i <= $numberOfPages; $i++) {
                $buttons[$i] = $this->generateButton($i);
            }
        }

        // Set the correct labels.
        foreach ($buttons as $page => &$button) {
            $inFirstBlock = $this->selectedPage <= 3 && $page <= 3;
            $inLastBlock  = $this->selectedPage >= $numberOfPages - 2 && $page >= $numberOfPages - 2;

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

            if ($label === '') {
                $button = null;
                continue;
            }

            $button['text'] = sprintf($label, $page);
        }

        return array_values(array_filter($buttons));
    }

    /**
     * Get the range of intermediate buttons for the keyboard.
     *
     * @return array
     */
    protected function generateRange(): array
    {
        $numberOfIntermediateButtons = $this->maxButtons - 2;
        $numberOfPages               = $this->getNumberOfPages();

        // @todo: Find a nicer solution for page 3
        $from = $this->selectedPage - 1;
        $to   = $this->selectedPage + ($this->selectedPage === 3 ? $numberOfIntermediateButtons - 1 : 2);

        if ($this->selectedPage === 1) {
            $from = 2;
            $to   = $this->maxButtons;
        } elseif ($this->selectedPage === $numberOfPages) {
            $from = $numberOfPages - $numberOfIntermediateButtons;
            $to   = $numberOfPages;
        } elseif ($this->selectedPage < 3) {
            $from = $this->selectedPage;
            $to   = $this->selectedPage + $numberOfIntermediateButtons;
        } elseif (($numberOfPages - $this->selectedPage) < 3) {
            $from = $numberOfPages - $numberOfIntermediateButtons;
            $to   = $numberOfPages;
        } elseif ($this->forceButtonCount) {
            $from = $this->selectedPage -
                floor($numberOfIntermediateButtons / 2);
            $to   = $this->selectedPage +
                ceil($numberOfIntermediateButtons / 2) +
                (int) ($this->selectedPage === 3 && $this->maxButtons > 5);
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
            'text'          => (string) $page,
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
