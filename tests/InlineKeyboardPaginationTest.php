<?php

namespace TelegramBot\InlineKeyboardPagination\Tests;

use TelegramBot\InlineKeyboardPagination\Exceptions\InlineKeyboardPaginationException;
use TelegramBot\InlineKeyboardPagination\InlineKeyboardPagination;

/**
 * Class InlineKeyboardPaginationTest
 */
class InlineKeyboardPaginationTest extends \PHPUnit\Framework\TestCase
{
    private InlineKeyboardPagination $ikp;
    private array $items = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    private int $itemsPerPage = 2;
    private int $selectedPage = 1;
    private string $command = 'command';

    public function setUp(): void
    {
        parent::setUpBeforeClass();

        $this->ikp = new InlineKeyboardPagination($this->items, $this->command, $this->selectedPage, $this->itemsPerPage);
    }

    public function testValidConstructor(): void
    {
        $data = $this->ikp->getPagination();

        self::assertCount($this->itemsPerPage, $data['items']);
        self::assertStringStartsWith("command={$this->command}", $data['keyboard'][0]['callback_data']);
    }

    public function testInvalidConstructor(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid page selected, must be between 1 and 10.');

        new InlineKeyboardPagination(range(1, 10), 'command', 10000, 1);
    }

    public function testEmptyItemsConstructor(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Items list empty.');

        new InlineKeyboardPagination([]);
    }

    public function testCallbackDataFormat(): void
    {
        $ikp = new InlineKeyboardPagination(range(1, 10), 'cmd', 2, 5);

        self::assertAllButtonPropertiesEqual([
            [
                'command=cmd&oldPage=2&newPage=1',
                'command=cmd&oldPage=2&newPage=2',
            ],
        ], 'callback_data', [$ikp->getPagination()['keyboard']]);

        $ikp->setCallbackDataFormat('{COMMAND};{OLD_PAGE};{NEW_PAGE}');

        self::assertAllButtonPropertiesEqual([
            [
                'cmd;2;1',
                'cmd;2;2',
            ],
        ], 'callback_data', [$ikp->getPagination()['keyboard']]);
    }

    public function testCallbackDataParser(): void
    {
        $data = $this->ikp->getPagination();

        $callback_data = $this->ikp::getParametersFromCallbackData($data['keyboard'][0]['callback_data']);

        self::assertSame([
            'command' => $this->command,
            'oldPage' => (string) $this->selectedPage,
            'newPage' => '1', // because we're getting the button at position 0, which is page 1
        ], $callback_data);
    }

    public function testValidPagination(): void
    {
        $length = (int) ceil(count($this->items) / $this->itemsPerPage);

        for ($i = 1; $i < $length; $i++) {
            $this->ikp->getPagination($i);
        }

        $this->assertSame($length, $this->ikp->getNumberOfPages());
    }

    public function testInvalidPagination(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid page selected, must be between 1 and 20.');

        $ikp = new InlineKeyboardPagination(range(1, 20), 'command', 1, 1);
        $ikp->getPagination($ikp->getNumberOfPages() + 1);
    }

    public function testSetMaxButtons(): void
    {
        $this->ikp->setMaxButtons(5);
        $this->ikp->setMaxButtons(6);
        $this->ikp->setMaxButtons(7);
        $this->ikp->setMaxButtons(8);

        self::assertTrue(true);
    }

    public function testMaxButtonsTooSmall(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid max buttons, must be between 5 and 8.');

        $this->ikp->setMaxButtons(4);
    }

    public function testMaxButtonsTooBig(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid max buttons, must be between 5 and 8.');

        $this->ikp->setMaxButtons(9);
    }

    public function testForceButtonsCountWith10Pages(): void
    {
        $datas = [
            [8, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 4, ['« 1', '‹ 2', '‹ 3', '· 4 ·', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 5, ['« 1', '‹ 2', '‹ 3', '‹ 4', '· 5 ·', '6 ›', '7 ›', '10 »']],
            [8, 6, ['« 1', '‹ 3', '‹ 4', '‹ 5', '· 6 ·', '7 ›', '8 ›', '10 »']],
            [8, 7, ['« 1', '‹ 4', '‹ 5', '‹ 6', '· 7 ·', '8 ›', '9 ›', '10 »']],
            [8, 8, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [8, 9, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [8, 10, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [7, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 4, ['« 1', '‹ 2', '‹ 3', '· 4 ·', '5 ›', '6 ›', '10 »']],
            [7, 5, ['« 1', '‹ 3', '‹ 4', '· 5 ·', '6 ›', '7 ›', '10 »']],
            [7, 6, ['« 1', '‹ 4', '‹ 5', '· 6 ·', '7 ›', '8 ›', '10 »']],
            [7, 7, ['« 1', '‹ 5', '‹ 6', '· 7 ·', '8 ›', '9 ›', '10 »']],
            [7, 8, ['« 1', '‹ 5', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [7, 9, ['« 1', '‹ 5', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [7, 10, ['« 1', '‹ 5', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [6, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '10 »']],
            [6, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '10 »']],
            [6, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '10 »']],
            [6, 4, ['« 1', '‹ 2', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [6, 5, ['« 1', '‹ 3', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [6, 6, ['« 1', '‹ 4', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [6, 7, ['« 1', '‹ 5', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [6, 8, ['« 1', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [6, 9, ['« 1', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [6, 10, ['« 1', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [5, 1, ['· 1 ·', '2', '3', '4 ›', '10 »']],
            [5, 2, ['1', '· 2 ·', '3', '4 ›', '10 »']],
            [5, 3, ['1', '2', '· 3 ·', '4 ›', '10 »']],
            [5, 4, ['« 1', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [5, 5, ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [5, 6, ['« 1', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [5, 7, ['« 1', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [5, 8, ['« 1', '‹ 7', '· 8 ·', '9', '10']],
            [5, 9, ['« 1', '‹ 7', '8', '· 9 ·', '10']],
            [5, 10, ['« 1', '‹ 7', '8', '9', '· 10 ·']],
        ];

        $ikp = new InlineKeyboardPagination(range(1, 10), 'cbdata', 1, 1);

        foreach ($datas as $data) {
            [$max_buttons, $page, $buttons] = $data;

            $ikp->setMaxButtons($max_buttons, true);
            self::assertAllButtonPropertiesEqual(
                [$buttons],
                'text',
                [$ikp->getPagination($page)['keyboard']],
                "MaxButtons: {$max_buttons}, Page: {$page}"
            );
        }
    }

    public function testForceButtonsCountWith3Pages(): void
    {
        $datas = [
            [8, 1, ['· 1 ·', '2', '3']],
            [8, 2, ['1', '· 2 ·', '3']],
            [8, 3, ['1', '2', '· 3 ·']],
            [7, 1, ['· 1 ·', '2', '3']],
            [7, 2, ['1', '· 2 ·', '3']],
            [7, 3, ['1', '2', '· 3 ·']],
            [6, 1, ['· 1 ·', '2', '3']],
            [6, 2, ['1', '· 2 ·', '3']],
            [6, 3, ['1', '2', '· 3 ·']],
            [5, 1, ['· 1 ·', '2', '3']],
            [5, 2, ['1', '· 2 ·', '3']],
            [5, 3, ['1', '2', '· 3 ·']],
        ];

        $ikp = new InlineKeyboardPagination(range(1, 3), 'cbdata', 1, 1);

        foreach ($datas as $data) {
            [$max_buttons, $page, $buttons] = $data;

            $ikp->setMaxButtons($max_buttons, true);
            self::assertAllButtonPropertiesEqual(
                [$buttons],
                'text',
                [$ikp->getPagination($page)['keyboard']],
                "MaxButtons: {$max_buttons}, Page: {$page}"
            );
        }
    }

    public function testFlexibleButtonsCount(): void
    {
        $datas = [
            [8, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '6 ›', '7 ›', '10 »']],
            [8, 4, ['« 1', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [8, 5, ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [8, 6, ['« 1', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [8, 7, ['« 1', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [8, 8, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [8, 9, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [8, 10, ['« 1', '‹ 4', '‹ 5', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [7, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '6 ›', '10 »']],
            [7, 4, ['« 1', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [7, 5, ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [7, 6, ['« 1', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [7, 7, ['« 1', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [7, 8, ['« 1', '‹ 5', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [7, 9, ['« 1', '‹ 5', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [7, 10, ['« 1', '‹ 5', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [6, 1, ['· 1 ·', '2', '3', '4 ›', '5 ›', '10 »']],
            [6, 2, ['1', '· 2 ·', '3', '4 ›', '5 ›', '10 »']],
            [6, 3, ['1', '2', '· 3 ·', '4 ›', '5 ›', '10 »']],
            [6, 4, ['« 1', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [6, 5, ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [6, 6, ['« 1', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [6, 7, ['« 1', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [6, 8, ['« 1', '‹ 6', '‹ 7', '· 8 ·', '9', '10']],
            [6, 9, ['« 1', '‹ 6', '‹ 7', '8', '· 9 ·', '10']],
            [6, 10, ['« 1', '‹ 6', '‹ 7', '8', '9', '· 10 ·']],
            [5, 1, ['· 1 ·', '2', '3', '4 ›', '10 »']],
            [5, 2, ['1', '· 2 ·', '3', '4 ›', '10 »']],
            [5, 3, ['1', '2', '· 3 ·', '4 ›', '10 »']],
            [5, 4, ['« 1', '‹ 3', '· 4 ·', '5 ›', '10 »']],
            [5, 5, ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »']],
            [5, 6, ['« 1', '‹ 5', '· 6 ·', '7 ›', '10 »']],
            [5, 7, ['« 1', '‹ 6', '· 7 ·', '8 ›', '10 »']],
            [5, 8, ['« 1', '‹ 7', '· 8 ·', '9', '10']],
            [5, 9, ['« 1', '‹ 7', '8', '· 9 ·', '10']],
            [5, 10, ['« 1', '‹ 7', '8', '9', '· 10 ·']],
        ];

        $ikp = new InlineKeyboardPagination(range(1, 10), 'cbdata', 1, 1);

        foreach ($datas as $data) {
            [$max_buttons, $page, $buttons] = $data;

            $ikp->setMaxButtons($max_buttons, false);
            self::assertAllButtonPropertiesEqual(
                [$buttons],
                'text',
                [$ikp->getPagination($page)['keyboard']],
                "MaxButtons: {$max_buttons}, Page: {$page}"
            );
        }
    }

    public function testGetCallbackDataFormat(): void
    {
        // Test default value.
        self::assertSame(
            'command={COMMAND}&oldPage={OLD_PAGE}&newPage={NEW_PAGE}',
            $this->ikp->getCallbackDataFormat()
        );

        // Test custom value.
        $customCallbackDataFormat = 'c={COMMAND}&op={OLD_PAGE}&np={NEW_PAGE}';
        $this->ikp->setCallbackDataFormat($customCallbackDataFormat);
        self::assertSame(
            $customCallbackDataFormat,
            $this->ikp->getCallbackDataFormat()
        );
    }

    public function testSelectedPageTooLow(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid page selected, must be between 1 and 10.');

        $ikp = new InlineKeyboardPagination(range(1, 10), 'command', 1, 1);
        $ikp->setSelectedPage(0);
    }

    public function testSelectedPageTooHigh(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid page selected, must be between 1 and 10.');

        $ikp = new InlineKeyboardPagination(range(1, 10), 'command', 1, 1);
        $ikp->setSelectedPage(11);
    }

    public function testGetItemsPerPage(): void
    {
        $ikp = new InlineKeyboardPagination(range(1, 10), 'command', 1, 4);

        self::assertEquals(4, $ikp->getItemsPerPage());

        $ikp->setItemsPerPage(2);

        self::assertEquals(2, $ikp->getItemsPerPage());
    }

    public function testInvalidItemsPerPage(): void
    {
        $this->expectException(InlineKeyboardPaginationException::class);
        $this->expectExceptionMessage('Invalid number of items per page, must be at least 1.');

        $this->ikp->setItemsPerPage(0);
    }

    public function testButtonLabels(): void
    {
        $cbdata  = 'command=%s&oldPage=%d&newPage=%d';
        $command = 'cbdata';
        $ikp1    = new InlineKeyboardPagination(range(1, 1), $command, 1, $this->itemsPerPage);
        $ikp10   = new InlineKeyboardPagination(range(1, $this->itemsPerPage * 10), $command, 1, $this->itemsPerPage);

        // current
        $keyboard = [$ikp1->getPagination(1)['keyboard']];
        self::assertAllButtonPropertiesEqual([
            ['· 1 ·'],
        ], 'text', $keyboard);
        self::assertAllButtonPropertiesEqual([
            [
                sprintf($cbdata, $command, 1, 1),
            ],
        ], 'callback_data', $keyboard);

        // first, previous, current, next, last
        $keyboard = [$ikp10->getPagination(5)['keyboard']];
        self::assertAllButtonPropertiesEqual([
            ['« 1', '‹ 4', '· 5 ·', '6 ›', '10 »'],
        ], 'text', $keyboard);
        self::assertAllButtonPropertiesEqual([
            [
                sprintf($cbdata, $command, 5, 1),
                sprintf($cbdata, $command, 5, 4),
                sprintf($cbdata, $command, 5, 5),
                sprintf($cbdata, $command, 5, 6),
                sprintf($cbdata, $command, 5, 10),
            ],
        ], 'callback_data', $keyboard);

        // first, previous, current, last
        $keyboard = [$ikp10->getPagination(9)['keyboard']];
        self::assertAllButtonPropertiesEqual([
            ['« 1', '‹ 7', '8', '· 9 ·', '10'],
        ], 'text', $keyboard);
        self::assertAllButtonPropertiesEqual([
            [
                sprintf($cbdata, $command, 9, 1),
                sprintf($cbdata, $command, 9, 7),
                sprintf($cbdata, $command, 9, 8),
                sprintf($cbdata, $command, 9, 9),
                sprintf($cbdata, $command, 9, 10),
            ],
        ], 'callback_data', $keyboard);

        // first, previous, current
        $keyboard = [$ikp10->getPagination(10)['keyboard']];
        self::assertAllButtonPropertiesEqual([
            ['« 1', '‹ 7', '8', '9', '· 10 ·'],
        ], 'text', $keyboard);
        self::assertAllButtonPropertiesEqual([
            [
                sprintf($cbdata, $command, 10, 1),
                sprintf($cbdata, $command, 10, 7),
                sprintf($cbdata, $command, 10, 8),
                sprintf($cbdata, $command, 10, 9),
                sprintf($cbdata, $command, 10, 10),
            ],
        ], 'callback_data', $keyboard);

        // custom labels, skipping some buttons
        // first, previous, current, next, last
        $labels = [
            'first'    => '',
            'previous' => 'previous %d',
            'current'  => null,
            'next'     => '%d next',
            'last'     => 'last',
        ];
        $ikp10->setLabels($labels);
        self::assertEquals($labels, $ikp10->getLabels());

        $keyboard = [$ikp10->getPagination(5)['keyboard']];
        self::assertAllButtonPropertiesEqual([
            ['previous 4', '6 next', 'last'],
        ], 'text', $keyboard);
        self::assertAllButtonPropertiesEqual([
            [
                sprintf($cbdata, $command, 5, 4),
                sprintf($cbdata, $command, 5, 6),
                sprintf($cbdata, $command, 5, 10),
            ],
        ], 'callback_data', $keyboard);
    }

    public static function assertButtonPropertiesEqual($value, $property, $keyboard, $row, $column, $message = ''): void
    {
        $row_raw    = array_values($keyboard)[$row];
        $column_raw = array_values($row_raw)[$column];

        self::assertSame($value, $column_raw[$property], $message);
    }

    public static function assertRowButtonPropertiesEqual(array $values, $property, $keyboard, $row, $message = ''): void
    {
        $column = 0;
        foreach ($values as $value) {
            self::assertButtonPropertiesEqual($value, $property, $keyboard, $row, $column++, $message);
        }
        self::assertCount(count(array_values($keyboard)[$row]), $values);
    }

    public static function assertAllButtonPropertiesEqual(array $all_values, $property, $keyboard, $message = ''): void
    {
        $row = 0;
        foreach ($all_values as $values) {
            self::assertRowButtonPropertiesEqual($values, $property, $keyboard, $row++, $message);
        }
        self::assertCount(count($keyboard), $all_values);
    }
}
