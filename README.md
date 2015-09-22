# Wistia PHP

[![Latest Version](https://img.shields.io/github/release/Automattic/wistia-php.svg?style=flat-square)](https://github.com/Automattic/wistia-php/releases)
[![Software License](https://img.shields.io/badge/license-GPLv2-brightgreen.svg?style=flat-square)](LICENSE)

A PHP client for consuming the Wistia API.

## Install

Via Composer

``` bash
$ composer require Automattic/wistia-php
```

## Methods & Properties

|Method|Parameters|
|---|---|
|`get_client`|`N/A`|
|`get_token`|`N/A`|
|`list_projects`|`N/A`|
|`show_project`|`(string) $project_hashed_id`|
|`create_project`|`(array) $project_data`|
|`update_project`|`(string) $project_hashed_id`, `(array) $project_data`|
|`delete_project`|`(string) $project_hashed_id`|
|`copy_project`|`(string) $project_hashed_id`|
|`list_sharings`|`(string) $project_hashed_id`|
|`show_sharing`|`(string) $project_hashed_id`, `(int) $sharing_id`|
|`create_sharing`|`(string) $project_hashed_id`|
|`update_sharing`|`(string) $project_hashed_id`, `(int) $sharing_id`, `(array) $sharing_data`|
|`delete_sharing`|`(string) $project_hashed_id`, `(int) $sharing_id`|
|`list_medias`|`N/A`|
|`show_media`|`(string) $media_hashed_id`|
|`create_media`|`(string) $file_path`, `(array) $media_data`|
|`update_media`|`(string) $media_hashed_id`, `(array) $media_data`|
|`delete_media`|`(string) $media_hashed_id`|
|`copy_media`|`(string) $media_hashed_id`|
|`stats_media`|`(string) $media_hashed_id`|
|`show_account`|`N/A`|
|`show_customizations`|`(string) $media_hashed_id`|
|`create_customizations`|`(string) $media_hashed_id`, `(array) $customizations_data`|
|`update_customizations`|`(string) $media_hashed_id`, `(array) $customizations_data`|
|`delete_customizations`|`(string) $media_hashed_id`|
|`list_captions`|`(string) $media_hashed_id`|
|`show_captions`|`(string) $media_hashed_id`, `(string) $language_code`|
|`create_captions`|`(string) $media_hashed_id`, `(array) $captions_data`|
|`update_captions`|`(string) $media_hashed_id`, `(array) $captions_data`|
|`delete_captions`|`(string) $media_hashed_id`, `(string) $language_code`|

|Properties|Type|
|---|---|
|`$client`|`object`|
|`$format`|`string`|
|`$last_response_code`|`int`|

## Constructor

When instantiating the library, you need to pass an array of parameters to the constructor.

The array must include the index `token` which contains your Wistia token.
Optionally the array can include the format of the responses, the value can be `json` (default), or `xml`. If it does not exist, `json` will be used.

## Tests

To run PHPUnit tests on this library, copy the file `tests/config.sample.php`, rename it to `config.php` and fill in the requested details.

Then open the terminal and navigate to the root of the library and use this command:

```
$ phpunit
```

*Note: Tests may fail if you run them too many times consecutively. There's a limit of 1000 requests/hour from Wistia, also the upload of dummy data may fail due to the internet connection problems and this will cause the tests to fail too.*

## Credits

- [Automattic](https://github.com/Automattic)
- [Nicola Mustone](https://github.com/SiR-DanieL)

## License

Wistia PHP - PHP wrapper to consume Wistia API

Copyright (C) 2015 Automattic

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

Please see [License File](LICENSE) for more information.
