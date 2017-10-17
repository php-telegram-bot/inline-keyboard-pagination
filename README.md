# [Telegram Bot Inline Keyboard Pagination][github-tgbot-ikp]

[![Scrutinizer Code Quality][code-quality-badge]][code-quality]
[![Codecov][code-coverage-badge]][code-coverage]
[![Build Status][build-status-badge]][build-status]

[![Latest Stable Version][latest-version-badge]][packagist-tgbot-ikp]
[![Total Downloads][total-downloads-badge]][packagist-tgbot-ikp]
[![License][license-badge]][license]

- [Installation](#installation)
    - [Composer](#composer)
- [Usage](#usage)
    - [Test Data](#test-data)
    - [How To Use](#how-to-use)
- [Code Quality](#code-quality)
- [License](#license)

## Installation

### Composer
```bash
composer require php-telegram-bot/inline-keyboard-pagination:^1.0.0
```

## Usage

### Test Data
```php
$items         = range(1, 100); // required. 
$command       = 'testCommand'; // optional. Default: pagination
$selected_page = 10;            // optional. Default: 1
$labels        = [              // optional. Change button labels (showing defaults)
    'default'  => '%d',
    'first'    => '« %d',
    'previous' => '‹ %d',
    'current'  => '· %d ·',
    'next'     => '%d ›',
    'last'     => '%d »',
];

// optional. Change the callback_data format, adding placeholders for data (showing default)
$callback_data_format = 'command={COMMAND}&oldPage={OLD_PAGE}&newPage={NEW_PAGE}'
```

### How To Use
```php
// Define inline keyboard pagination.
$ikp = new InlineKeyboardPagination($items, $command);
$ikp->setMaxButtons(7, true); // Second parameter set to always show 7 buttons if possible.
$ikp->setRangeOffset(1); //optional: if you change offsets of selected page, you can use this method. e.g if selected page is 6 and Range offset set as 1, you will have 5 & 7 in pagination
$ikp->setLabels($labels);

/*
 *optional: But recommended. if you want that max_page will set according to labels you defined,
 * please call this method. if you remove $label elements and then call this method, max_page will be defined according to labels
 */
$ikp->setMaxButtonsBasedOnLabels();
$ikp->setCallbackDataFormat($callback_data_format);

// Get pagination.
$pagination = $ikp->getPagination($selected_page);

// or, in 2 steps.
$ikp->setSelectedPage($selected_page);
$pagination = $ikp->getPagination();
```

Now, `$pagination['keyboard']` is basically a row that contains the pagination.

```php
// Use it in your request.
if (!empty($pagination['keyboard'])) {
    //$pagination['keyboard'][0]['callback_data']; // command=testCommand&oldPage=10&newPage=1
    //$pagination['keyboard'][1]['callback_data']; // command=testCommand&oldPage=10&newPage=7
    
    ...
    $data['reply_markup' => [
        'inline_keyboard' => [
            $pagination['keyboard'],
        ],
    ];
    ...
}
```

To get the callback data, you can use the provided helper method (only works when using the default callback data format):
```php
// e.g. Callback data.
$callback_data = 'command=testCommand&oldPage=10&newPage=1';

$params = InlineKeyboardPagination::getParametersFromCallbackData($callback_data);

//$params = [
//    'command' => 'testCommand',
//    'oldPage' => '10',
//    'newPage' => '1',
//];

// or, just use PHP directly if you like. (literally what the helper does!)
parse_str($callback_data, $params);
```

## Code Quality

Run the PHPUnit tests via Composer script. 

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File][license] for more information.

Project based on [Telegram Bot Pagination][github-lartie-tbp] by [lartie][github-lartie].


[github-tgbot-ikp]: https://github.com/php-telegram-bot/inline-keyboard-pagination "PHP Telegram Bot Inline Keyboard Pagination on GitHub"
[packagist-tgbot-ikp]: https://packagist.org/packages/php-telegram-bot/inline-keyboard-pagination "PHP Telegram Bot Inline Keyboard Pagination on Packagist"
[license]: https://github.com/php-telegram-bot/inline-keyboard-pagination/blob/master/LICENSE "PHP Telegram Bot Inline Keyboard Pagination license"

[code-quality-badge]: https://img.shields.io/scrutinizer/g/php-telegram-bot/inline-keyboard-pagination.svg
[code-quality]: https://scrutinizer-ci.com/g/php-telegram-bot/inline-keyboard-pagination/?branch=master "Code quality on Scrutinizer"
[code-coverage-badge]: https://img.shields.io/codecov/c/github/php-telegram-bot/inline-keyboard-pagination.svg
[code-coverage]: https://codecov.io/gh/php-telegram-bot/inline-keyboard-pagination "Code coverage on Codecov"
[build-status-badge]: https://img.shields.io/travis/php-telegram-bot/inline-keyboard-pagination.svg
[build-status]: https://travis-ci.org/php-telegram-bot/inline-keyboard-pagination "Build status on Travis-CI"

[latest-version-badge]: https://img.shields.io/packagist/v/php-telegram-bot/inline-keyboard-pagination.svg
[total-downloads-badge]: https://img.shields.io/packagist/dt/php-telegram-bot/inline-keyboard-pagination.svg
[license-badge]: https://img.shields.io/packagist/l/php-telegram-bot/inline-keyboard-pagination.svg

[github-lartie-tbp]: https://github.com/lartie/Telegram-Bot-Pagination "Telegram Bot Pagination by Lartie on GitHub"
[github-lartie]: https://github.com/lartie "Lartie on GitHub"
