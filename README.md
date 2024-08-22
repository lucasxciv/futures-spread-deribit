# Futures Spread Deribit 💰

Web application to help practice the studies of the futures' spread strategy.

> *Spread trades are executed to attempt to profit from the widening or narrowing of the spread, rather than from movement in the prices of the legs directly. Spreads are either "bought" or "sold" depending on whether the trade will profit from the widening or narrowing of the spread, [Spread trade - Wikipedia.](https://en.wikipedia.org/wiki/Spread_trade)*

NOTE: it uses a [polling strategy](https://en.wikipedia.org/wiki/Polling_(computer_science)) to send notifications.

## Requirements

To install and run the project you need to have Composer and PHP ^8.3 installed on your machine, or you can use Docker.

## Installation

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
