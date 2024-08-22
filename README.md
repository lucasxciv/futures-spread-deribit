# Futures Spread Deribit 💰

Web application to help practice the studies of the futures' spread strategy.

> *Spread trades are executed to attempt to profit from the widening or narrowing of the spread, rather than from movement in the prices of the legs directly. Spreads are either "bought" or "sold" depending on whether the trade will profit from the widening or narrowing of the spread, [Spread trade - Wikipedia.](https://en.wikipedia.org/wiki/Spread_trade)*

## Requirements

To install and run the project you need to have Composer and PHP ^8.3 installed on your machine, or you can use Docker.

## Installation

To run the application, you need to install the Composer dependencies with `composer install` command, and start the server with `php -S localhost:8000 -t public/` command.

In Docker, first build the image with `docker build . -t futures-spread-deribit`, and then run the container with `docker run -p 8080:8080 futures-spread-deribit`. Access the application at http://localhost:8080.

NOTE: it uses a [polling strategy](https://en.wikipedia.org/wiki/Polling_(computer_science)) to send notifications.

## Contributing ##

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

**Futures Spread Deribit** project is released under the MIT License. Please see [License File](LICENSE) for more information.
