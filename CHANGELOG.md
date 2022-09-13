# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com), and this project adheres to [Semantic Versioning](https://semver.org).

## Unreleased

## [v2.1.0 - 2022-09-13](https://github.com/pxgamer/trakt-to-yts/compare/v2.0.1...v2.1.0)

### Added
- Add Docker support ([665508c](https://github.com/pxgamer/trakt-to-yts/commit/665508c2053a91741ab580ce538a8004e072fd20))
- Add support for 4K (2160p) downloads ([ad4f6bb](https://github.com/pxgamer/trakt-to-yts/commit/ad4f6bb8f6be3bbc5f435f8b92e7e71e1fa76103))

### Changed
- Move `--list` to be an argument ([06e5a85](https://github.com/pxgamer/trakt-to-yts/commit/06e5a85b2b57f15a4be5fb9bf53cd3816cfaa027))

### Fixed
- Ensure Trakt user is provided ([7b6a5ab](https://github.com/pxgamer/trakt-to-yts/commit/7b6a5ab8498049e7e76f790d82255489ee742a5c))

## [v2.0.1 - 2022-09-05](https://github.com/pxgamer/trakt-to-yts/compare/v2.0.0...v2.0.1)

### Changed
- Clean up command ([9327263](https://github.com/pxgamer/trakt-to-yts/commit/9327263322b4d169c5318f54c1f12a790e2cd363), [1c7f576](https://github.com/pxgamer/trakt-to-yts/commit/1c7f576493ea0780d0f5a25ce0e216771b3b9e8f))
- Add fallbacks for missing inputs ([b57732d](https://github.com/pxgamer/trakt-to-yts/commit/b57732d6fb11b09450afefe979109ac47036c38d))

## [v2.0.0 - 2022-09-04](https://github.com/pxgamer/trakt-to-yts/compare/v1.0.0...v2.0.0)

### Changed
- Rewrite using Laravel Zero ([#10](https://github.com/pxgamer/trakt-to-yts/pull/10))
- Require PHP 8.1 or later ([#10](https://github.com/pxgamer/trakt-to-yts/pull/10))

## [v1.0.0 - 2018-11-08](https://github.com/pxgamer/trakt-to-yts/compare/v0.3.1...v1.0.0)

### Changed
- Update Box to v3.2 ([b3d237f4](https://github.com/pxgamer/trakt-to-yts/commit/b3d237f477f344024faa4691a3433c9438be3e1d))
- Optimise function imports ([20c312c6](https://github.com/pxgamer/trakt-to-yts/commit/20c312c698eb157c99eec2678a80d93ebbfe143a))

## [v0.3.1 - 2018-07-06](https://github.com/pxgamer/trakt-to-yts/compare/v0.3.0...v0.3.1)

### Added
- Add automated Travis Phar releases ([#8](https://github.com/pxgamer/trakt-to-yts/issues/8))

## [v0.3.0 - 2018-05-23](https://github.com/pxgamer/trakt-to-yts/compare/v0.2.0...v0.3.0)

### Added
- Add a notice for torrents that fail to be shown ([#5](https://github.com/pxgamer/trakt-to-yts/issues/5))
- Add a new `--statistics` option for stats output ([#6](https://github.com/pxgamer/trakt-to-yts/issues/6))

### Changed
- Change to add visibility keywords to all class constants ([22b41a0](https://github.com/pxgamer/trakt-to-yts/commit/22b41a0f017b60a0bc59bf11c2415d24a4f8c003))

## [v0.2.0 - 2018-04-17](https://github.com/pxgamer/trakt-to-yts/compare/v0.1.0...v0.2.0)

### Changed
- Change to use Guzzle for torrent downloads ([#4](https://github.com/pxgamer/trakt-to-yts/issues/4))

### Fixed
- Fix issue with the download name

## v0.1.0 - 2018-04-17

### Added
- Initial release
