# Requirements
- Docker

# Installation Instructions
- From the base folder in Terminal:
- Run `make build` to build the container and all of the dependencies.
- Run `make up` to run the Docker container.
- Run `make install` to install all composer dependencies. (Shouldn't take long as there aren't many).

# Testing
- Run `make run-test` to run the PHPUnit tests.

# Additional Commands
- Starting with `make`.
- - `down` to stop the Docker container.
- - `restart` to down and up the Docker container.
- - `rebuild` to down, build and up the Docker container.
- - `update` to update vendor files via composer.
- - `lint` to run lint on the code.
- - `lint-fix` to run lint on the code and correct errors.