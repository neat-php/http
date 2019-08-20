# Changelog
All notable changes to Neat HTTP components will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.3.0] - 2019-08-20
### Changed
- Store and fetch only one error per input field.
- Add string type hints to withHeader and withoutHeader method signatures.
- Add type hints to Input class method signatures.
- Refactor internal Message (Request and Response base class) implementation.

### Fixed
- Fixed error in Url->withQueryParameters().
- Fixed broken ResponseFactory reference.

## [0.2.3] - 2019-08-15
### Changed
- Rename ReceiverInterface to Receiver and TransmitterInterface to Transmitter.

### Deprecated
- The ReceiverInterface and TransmitterInterface interfaces are deprecated as of now.

## [0.2.2] - 2019-08-02
### Added
- Added Url->isSecure(): bool method.
 
### Fixed
- Upload->move() doesn't throw Runtime exceptions when it succeeds.

## [0.2.1] - 2019-08-01
### Added
- Added doc blocks to the TransmitterInterface.

### Changed
- Router->match() now returns the remaining segments for wildcards.

## [0.2.0] - 2019-07-24
### Added
- Recursive router implementation.
- TransmitterInterface and ReceiverInterface.

### Changed
- Moved to a PSR7 wrapper instead.
