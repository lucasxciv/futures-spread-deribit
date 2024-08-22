# Futures Spread Deribit 💰

[![Continuous Integration](https://github.com/lucasxciv/futures-spread-deribit/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/lucasxciv/futures-spread-deribit/actions/workflows/continuous-integration.yml)
[![Hits-of-Code](https://hitsofcode.com/github/lucasxciv/futures-spread-deribit?branch=main)](https://hitsofcode.com/github/lucasxciv/futures-spread-deribit)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://github.com/lucasxciv/futures-spread-deribit/blob/main/LICENSE)

Web application to help practice the studies of the futures' spread strategy.

> *Spread trades are executed to attempt to profit from the widening or narrowing of the spread, rather than from movement in the prices of the legs directly. Spreads are either "bought" or "sold" depending on whether the trade will profit from the widening or narrowing of the spread, [Spread trade - Wikipedia.](https://en.wikipedia.org/wiki/Spread_trade)*

NOTE: it uses a [polling strategy](https://en.wikipedia.org/wiki/Polling_(computer_science)) to send notifications.

## Requirements

Composer and PHP ^8.3, or Docker.

## Installation

Create the file `.env` based on the `.env.example` file, and `.env.production` to use in the Docker container.

### Locally

Install dependencies:
```
composer install
```

Start server:
```
php -S localhost:8000 -t public/
```

Access: http://localhost:8000

### Docker

Build image:
```
docker build . -t futures-spread-deribit
```

Run container:
```
docker run -p 8080:8080 futures-spread-deribit
```

Access: http://localhost:8080.

## Contributing ##

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

This project is released under the MIT License. Please see [License File](LICENSE) for more information.
