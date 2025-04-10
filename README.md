# Requirements
- Docker

# Installation Instructions
- From the base folder in Terminal:
- Run `make build` to build the container and all of the dependencies.
- Run `make up` to run the Docker container.
- (Optional) Run `make install` to install all composer dependencies, and upgrade them if required `make update`. (Shouldn't take long as there aren't many).

# Testing
- Run `make run-test` to run the PHPUnit tests.

# Additional Commands
- Starting with `make`.
    - `down` to stop the Docker container.
    - `restart` to down and up the Docker container.
    - `rebuild` to down, build and up the Docker container.
    - `update` to update vendor files via composer.
    - `lint` to run lint on the code.
    - `lint-fix` to run lint on the code and correct errors.

# Testee Comments
I've really rather enjoyed working on this test, it was refreshing to work on fairly raw PHP without the bulk of a framework.

I'm quite confident that I've done everything the test asks for, and I've written the code from the perspective of the Statistics API being a WIP.

## Things I've inferred, or kept in mind while coding:
- As instructed by the test sheet, I've kept third party libraries to a minimum, only using PHPUnit for testing purposes.
    - Because of this, I quickly mocked up a faux Statistics API endpoint.
- I've used PHP 8.4.5, the most recent release as of the time of writing.
- It is assumed that the data returned by the InterestAccount would be stored or otherwise handled externally to the library.
    - The unit tests have "stored" data, mocked up as though they are passing in stored data to the library.
- The Stats API is "probed" via the InterestAccount library. This is because the combined requirements of the interest account would mostly happen *before* if the API is called prior to the library, nullifying their reason to exist.
    - An example of this is the userID requirement being UUIDv4, there would be no need to validate it at all in the library when making an account. This is because the API would be validated at the time, and invalid data and errors should not be passed into the library not made to handle them.
        - That being said, I would have some error handling for this regardless.
    - Another example is the FAQ stating that you want to see how I would consume an API response. As above, properly handled responses called before the library should only allow legimate responses into the library, and passing only the vetted body into the library defeats the purpose of wanting to see how I would consume a response.
    - Ultimately, this could easily be called beforehand (including in the unit tests) if the change needed to be made.
- The Stats API would identify whether an account has already been made or not, returning an error response when asking for a new account's details.
    - If this is not the case, the user would not be able to try creating a new account, as this should be validated beforehand. If the library *can* be called to create a new account and should perform verification, this can easily be added with a boolean (such as activeAccount) check, or similar.
- Interest rate remaining constant means that it just cannot be changed once set.
- As balance is stored in pennies, *and* interest <1p isn't applied, I've floored applyable interest to remove the decimals. I considered passing the remaining decimal value onwards to the next payment, but that was not stipulated in the test requirements. However, I know that there are a number of different accepted ways of handling fractions of pennies, including to five decimal places.
- I have taken the possibility of a leap year into account regarding annual interest rate, and allowed a forced override one way or the other for testing purposes.
- I would expect that normally, the interest calculation happenening everything three days would be called via some form of Chronjob or similar. However, as the requirements stipulate "interest calculation and payout happens every three days", I took this to mean that it could be calculated in bulk and ran this in a loop. I could have passed in a from date and to date rather than days, but that would have only amounted to an extra calculation, not much difference and arguably would have been calculated beforehand.
- Deposits also list as transactions, and the interest payout runs through this.
- As only one account can be in use by the library at any one time, it is not possible to deposit funds into another person's account. This is on the premise that the data being passed in has already been vetted, as this is just a library.
- Effectively every outcome has been tested for in unit tests.

The code has been linted to match PSR12 standards.

I haven't embellished my commits, I genuinely treat commits like a form of patch notes and try to keep to the proper git conventions, including the commit message character limit. I'll only add a commit message and no description if the change was small enough to summarise in the message. I dislike seeing an enormous commit message with no description.

Thank you for the test, and taking the time to review my work.


# New reader comments:
This is an obfuscated test for a company.
