# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2022-03-05

### Changed
- Tidying up the documentation.

### Added
- Documentation for the methods `::getStationCheckResults()` and `::getStationClicks()`.
- Added the `$limit` parameter for the `::getStationCheckResults()` method.

### Fixed
 - Bug: without specifying the uuid, `::getStationCheckResults()` and `::getStationClicks()` would return empty results.

## [2.0.1] - 2021-11-01

### Changed

Added `guzzlehttp/guzzle` to the project, replacing my own `adinan-cenci/simple-request`.

## [2.0.0] - 2021-05-22

### Changed

The `RadioBrowser` class is now a wrapper for the new `RadioBrowserApi` class.
`RadioBrowserApi` is the general one, its methods return strings, `RadioBrowser` methods return arrays or objects depending on the parameters informed to the constructor.

### Added

- RadioBrowserApi class
- The read-only proprieties $server and $format.
- ::getStationsByClicks()
- ::getStationsByVotes()
- ::getStationsByRecentClicks()
- ::getStationsByLastChange()
- ::getStationOlderVersions()
- ::getBrokenStations()
- ::getServerStats()
- ::getServerMirrors()
- ::getServerConfig()
- ::getServerMetrics()
- ::addStation()
- added examples files

## [0.1.1] - 2021-01-28

### Fixed
- `::getStationsByExactTag($tag)` and `::getStationsByTag($tag)` no longer strip "#" from `$tag` before request.
