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
	 * @var integer
	 */
	protected $items_per_page;

	/**
	 * @var integer
	 */
	protected $max_buttons = 5;

	/**
	 * @var bool
	 */
	protected $force_button_count = false;

	/**
	 * @var integer
	 */
	protected $selected_page;

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * @var integer
	 */
	protected $range_offset = 1;

	/**
	 * @var string
	 */
	protected $command;

	/**
	 * @var string
	 */
	protected $callback_data_format = 'command={COMMAND}&oldPage={OLD_PAGE}&newPage={NEW_PAGE}';

	/**
	 * @var array
	 */
	protected $labels = [
		'default'  => '%d',
		'first'    => '« %d',
		'previous' => '‹ %d',
		'current'  => '· %d ·',
		'next'     => '%d ›',
		'last'     => '%d »',
	];

	/**
	 * @inheritdoc
	 * @throws InlineKeyboardPaginationException
	 */
	public function setMaxButtons(int $max_buttons = 5, bool $force_button_count = false): InlineKeyboardPagination
	{
		if ($max_buttons < 3 || $max_buttons > 8) {
			throw new InlineKeyboardPaginationException('Invalid max buttons, must be between 3 and 8.');
		}
		$this->max_buttons        = $max_buttons;
		$this->force_button_count = $force_button_count;

		return $this;
	}

	/**
	 * Get the current callback format.
	 *
	 * @return string
	 */
	public function getCallbackDataFormat(): string
	{
		return $this->callback_data_format;
	}

	/**
	 * Set the callback_data format.
	 *
	 * @param string $callback_data_format
	 *
	 * @return InlineKeyboardPagination
	 */
	public function setCallbackDataFormat(string $callback_data_format): InlineKeyboardPagination
	{
		$this->callback_data_format = $callback_data_format;

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
	public function setLabels($labels): InlineKeyboardPagination
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
	public function setSelectedPage(int $selected_page): InlineKeyboardPagination
	{
		$number_of_pages = $this->getNumberOfPages();
		/*if ($selected_page < 1 || $selected_page > $number_of_pages) {
			throw new CustomInlineKeyboardPaginationException('Invalid selected page, must be between 1 and ' . $number_of_pages);
		}*/

		// if current page is greater than total pages...
		if ($selected_page > $number_of_pages) {
			// set current page to last page
			$selected_page = $number_of_pages;
		}
		// if current page is less than first page...
		if ($selected_page < 1) {
			// set current page to first page
			$selected_page = 1;
		}
		$this->selected_page = $selected_page;

		return $this;
	}

	/**
	 * Get the number of items shown per page.
	 *
	 * @return int
	 */
	public function getItemsPerPage(): int
	{
		return $this->items_per_page;
	}

	/**
	 * Set how many items should be shown per page.
	 *
	 * @param int $items_per_page
	 *
	 * @return InlineKeyboardPagination
	 * @throws InlineKeyboardPaginationException
	 */
	public function setItemsPerPage($items_per_page): InlineKeyboardPagination
	{
		if ($items_per_page <= 0) {
			throw new InlineKeyboardPaginationException('Invalid number of items per page, must be at least 1');
		}
		$this->items_per_page = $items_per_page;

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
	 * Set max number of pages based on labels which user defined
	 *
	 * @return InlineKeyboardPagination
	 * @throws InlineKeyboardPaginationException
	 */
	public function setMaxPageBasedOnLabels(): InlineKeyboardPagination
	{
		$max_buttons = 0;
		$count = count($this->labels);
		if ($count < 2) {
			throw new InlineKeyboardPaginationException('Invalid number of labels was passed to paginator');
		}

		if (isset($this->labels['current'])) {
			$max_buttons++;
		}

		if (isset($this->labels['first'])) {
			$max_buttons++;
		}

		if (isset($this->labels['last'])) {
			$max_buttons++;
		}

		if (isset($this->labels['previous'])) {
			$max_buttons++;
		}

		if (isset($this->labels['next'])) {
			$max_buttons++;
		}
		$max_buttons += $this->range_offset*2;

		$this->max_buttons = $max_buttons;

		return $this;
	}

	/**
	 * Set offset of range
	 *
	 * @return InlineKeyboardPagination
	 * @throws InlineKeyboardPaginationException
	 */
	public function setRangeOffset($offset): InlineKeyboardPagination
	{
		if ($offset < 0 || !is_numeric($offset)) {
			throw new InlineKeyboardPaginationException('Invalid offset for range');
		}

		$this->range_offset = $offset;

		return $this;
	}

	/**
	 * Calculate and return the number of pages.
	 *
	 * @return int
	 */
	public function getNumberOfPages(): int
	{
		return (int) ceil(count($this->items) / $this->items_per_page);
	}

	/**
	 * TelegramBotPagination constructor.
	 *
	 * @inheritdoc
	 * @throws InlineKeyboardPaginationException
	 */
	public function __construct(array $items, string $command = 'pagination', int $selected_page = 1, int $items_per_page = 5)
	{
		$this->setCommand($command);
		$this->setItemsPerPage($items_per_page);
		$this->setItems($items);
		$this->setSelectedPage($selected_page);
	}

	/**
	 * @inheritdoc
	 * @throws InlineKeyboardPaginationException
	 */
	public function getPagination(int $selected_page = null): array
	{
		if ($selected_page !== null) {
			$this->setSelectedPage($selected_page);
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
		$buttons         = [];
		$number_of_pages = $this->getNumberOfPages();

		if ($number_of_pages === 1) {
			return $buttons;
		}

		if ($number_of_pages > $this->max_buttons) {
			if ($this->selected_page > 1) {
				// get previous page num
				$buttons[] = $this->generateButton($this->selected_page - 1, 'previous');
			}
			// for first pages
			if($this->selected_page > $this->range_offset + 1 && $number_of_pages >= $this->max_buttons){
				$buttons[] = $this->generateButton(1, 'first');
			}

			$range_offsets = $this->generateRange();
			// loop to show links to range of pages around current page
			for ($i = $range_offsets['from'] ; $i < $range_offsets['to'] ; $i++) {
				// if it's a valid page number...
				if ($i == $this->selected_page) {
					$buttons[] = $this->generateButton($this->selected_page, 'current');
				} elseif (($i > 0) && ($i <= $number_of_pages)) {
					$buttons[] = $this->generateButton($i, 'default');
				}
			}

			// if not on last page, show forward and last page links
			if($this->selected_page + $this->range_offset < $number_of_pages  && $number_of_pages >= $this->max_buttons){
				$buttons[] = $this->generateButton($number_of_pages, 'last');
			}
			if ($this->selected_page != $number_of_pages && $number_of_pages > 1) {
				$buttons[] = $this->generateButton($this->selected_page + 1, 'next');
			}
		} else {
			for ($i = 1; $i <= $number_of_pages; $i++) {
				// if it's a valid page number...
				if ($i == $this->selected_page) {
					$buttons[] = $this->generateButton($this->selected_page, 'current');
				} elseif (($i > 0) && ($i <= $number_of_pages)) {
					$buttons[] =  $this->generateButton($i, 'default');
				}
			}
		}

		// Set the correct labels.
		foreach ($buttons as $page => &$button) {

			$label_key = $button['label'];

			$label = $this->labels[$label_key] ?? '';

			if ($label === '') {
				$button = null;
				continue;
			}

			$button['text'] = sprintf($label, $button['text']);
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
		$number_of_pages                = $this->getNumberOfPages();

		$from = $this->selected_page - $this->range_offset;
		$to = (($this->selected_page + $this->range_offset) + 1);
		$last = $number_of_pages - $this->selected_page;
		if($number_of_pages - $this->selected_page <= $this->range_offset)
			$from -= ($this->range_offset) - $last;
		if($this->selected_page < $this->range_offset + 1 )
			$to += ($this->range_offset + 1) - $this->selected_page;

		return compact('from', 'to');
	}

	/**
	 * Generate the button for the passed page.
	 *
	 * @param int $page
	 * @param string $label
	 *
	 * @return array
	 */
	protected function generateButton(int $page, string $label): array
	{
		return [
			'text'          => (string) $page,
			'callback_data' => $this->generateCallbackData($page),
			'label'         => $label,
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
			[$this->command, $this->selected_page, $page],
			$this->callback_data_format
		);
	}

	/**
	 * Get the prepared items for the selected page.
	 *
	 * @return array
	 */
	protected function getPreparedItems(): array
	{
		return array_slice($this->items, $this->getOffset(), $this->items_per_page);
	}

	/**
	 * Get the items offset for the selected page.
	 *
	 * @return int
	 */
	protected function getOffset(): int
	{
		return $this->items_per_page * ($this->selected_page - 1);
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
	public static function getParametersFromCallbackData($data): array
	{
		parse_str($data, $params);

		return $params;
	}
}
