# Tests with the Container

Using a random nickname in a test is weird: we should be explicit about our
input and output. Just set it to `ObjectOrienter`. Now it's easy to make our
asserts more specific, like for the `Location` header using `assertEquals`,
which should be `/api/programmers/ObjectOrienter`. And now use the method
`getHeader()`:

[[[ code('25bcb82159') ]]]

And at the bottom, `assertArrayHasKey` is good, but we really want to say
`assertEquals()` to really check that the `nickname` key coming back is set
to `ObjectOrienter`:

[[[ code('22f44a4ae7') ]]]

This test makes me happier. But does it pass? Run it!

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Sawheet! All green. Untilllllll you try it again:

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Now it explodes - 500 status code and we can't even see the error. But I
know it's happening because `nickname` is unique in the database, and now
we've got the nerve to try to create a second ObjectOrienter.

## Booting the Container

Ok, we've gotta take control of the stuff in our database - like by clearing
everything out before each test.

If we had the EntityManager object, we could use it to help get that done.
So, let's boot the framework right inside `ApiTestCase`. But not to make
any requests, just so we can get the container and use our services.

Symfony has a helpful way to do this - it's a base class called `KernelTestCase`:

[[[ code('348cac951b') ]]]

Inside `setupBeforeClass()`, say `self::bootKernel()`:

[[[ code('ef83df932f') ]]]

The kernel is the heart of Symfony, and booting it basically just makes the
service container available.

Add the `tearDown()` method... and do nothing. What!? This is important.
I'm adding a comment about why - I'll explain in a second:

[[[ code('c14a7a3d7e') ]]]

But first, create a `private function getService()` with an `$id` argument.
Woops - make that `protected` - the whole point of this method is to let
our test classes fetch services from the container. To do that, return
`self::$kernel->getContainer()->get($id)`:

[[[ code('187fdc0973') ]]]

The whole point of that `KernelTestCase` base class is to set and boot that
static `$kernel` property which has the container on it. Now normally, the
base class actually shuts down the kernel in `tearDown()`. What I'm doing -
on purpose - is booting the kernel and creating the container just once
per my whole test suite.

That'll make things faster, though in theory it could cause issues or even
slow things down eventually. You can experiment by shutting down your kernel
in `tearDown()` and booting it in `setup()` if you want. Or even just clearing
the EntityManager to avoid a lot of entities getting stuck inside of it after
a bunch of tests.

## Clearing Data

Because we have the container, we have the EntityManager. And that also means
we have an easy way to clear data. Create a new private function called `purgeDatabase()`.
Because we have the Doctrine [DataFixtures](https://github.com/doctrine/data-fixtures)
library installed, we can use a great class called `ORMPurger`. Pass it the
EntityManager - so `$this->getService('doctrine.orm.default_entity_manager')`. To clear
things out, say `$purger->purge()`:

[[[ code('4bb6b5ca7a') ]]]

Now we just need to call this before every test - so calling this in `setup()`
is the perfect spot - `$this->purgeDatabase()`:

[[[ code('5357b3e485') ]]]

This should clear the `ObjectOrienter` out of the database and hopefully
get things passing. Try the test!

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Drumroll! Oh no - still a 500 error. And we still can't see the error. Time
to take our debugging tools up a level.
