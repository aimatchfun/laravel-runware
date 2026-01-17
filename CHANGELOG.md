# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.3.2] - 2026-01-XX

### Changed
- Updated PHP requirement from ^8.3 to ^8.4 to match php-runware-sdk requirements
- Updated php-runware-sdk dependency from ^2.6 to ^3.4
- Updated all references from `TextToImage` to `ImageInference` class
- Updated GitHub Actions workflow to test with PHP 8.4 only

### Fixed
- Fixed tests to work with `RunwareResponse` return type from SDK v3.0+
- Fixed `ImageUpload` test mocks to use correct response format (`data` as array with first element)
- Updated test examples and documentation to reflect new `RunwareResponse` usage

### Added
- Added support for `RunwareResponse` DTO in tests and examples
- Updated documentation with examples showing how to use `RunwareResponse`
