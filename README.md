# trakt-to-yts

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Style CI][ico-styleci]][link-styleci]
[![Code Coverage][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A CLI for downloading Trakt lists from YTS.

## Install

Via Composer

```bash
$ composer require pxgamer/trakt-to-yts
```

Via Phive

```bash
$ phive install pxgamer/trakt-to-yts
```

## Usage

- Get an API key from [Trakt][trakt-api]
- Either set the `TRAKT_API_KEY` environment variable, or provide a `--key {key}` option in the command
- Run the binary using `trakt-to-yts [options]`

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email owzie123@gmail.com instead of using the issue tracker.

## Credits

- [pxgamer][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[trakt-api]: https://trakt.tv/oauth/applications

[ico-version]: https://img.shields.io/packagist/v/pxgamer/trakt-to-yts.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/pxgamer/trakt-to-yts/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/129815869/shield
[ico-code-quality]: https://img.shields.io/codecov/c/github/pxgamer/trakt-to-yts.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/pxgamer/trakt-to-yts.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/pxgamer/trakt-to-yts
[link-travis]: https://travis-ci.org/pxgamer/trakt-to-yts
[link-styleci]: https://styleci.io/repos/129815869
[link-code-quality]: https://codecov.io/gh/pxgamer/trakt-to-yts
[link-downloads]: https://packagist.org/packages/pxgamer/trakt-to-yts
[link-author]: https://github.com/pxgamer
[link-contributors]: ../../contributors
