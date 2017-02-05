# Neo4j Movies Example Project in Symfony

This project aims to introduce the usage of the neo4j/neo4j-bundle as mentioned on https://neo4j.com/developer/example-project/.
The intent is not to make proper code, but something as understandable as it can be.

## Installation
### Fixtures
In order to have the proper data set, you should follow the instructions available on: https://neo4j.com/developer/example-project/#_data_setup
We recommend you to use the first option which is probably the fastest one.

### Code dependencies
From the project root directory, simply run 
```bash
$ composer install
```

## Run

When the data fixtures and depedencies are installed you may run the application. Simply start PHP's web server by: 
```bash
$ php bin/console server:run
```

Now navigate to http://127.0.0.1:8000. You can try search for movies like "Matrix", "Top Gun", "Apollo 13" and many more.

## The code

The interesting code is placed in `src/AppBundle/Controller/MovieController`. 

## License
This code is released under the MIT license. See the attached `LICENSE` file for further informations.

## Contributing
Found a bug? Please help us and [report it](https://github.com/neo4j-examples/movies-symfony-php-bolt/issues). Found the solution to that problem? We'll be glad to accept [your pull requests](https://github.com/neo4j-examples/movies-symfony-php-bolt/pulls)! 
