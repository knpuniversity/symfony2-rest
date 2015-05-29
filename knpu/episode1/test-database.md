# Using a Test Database

We're using the built-in PHP web server running on port 8000. We
have that hardcoded at the top of `ApiTestCase`: when the Client is
created, it *always* goes to `localhost:8000`. Bummer! All of our fellow
code battlers will need to have the exact same setup.

We need to make this configurable - create a new variable `$baseUrl` and set it
to an environment variable called `TEST_BASE_URL` - I'm making that name
up. Use this for the `base_url` option:

[[[ code('8ed091385d') ]]]

There are endless ways to set environment variables. But we want to at least
give this a default value. Open up `app/phpunit.xml.dist`. Get rid of those
comments - we want a `php` element with an `env` node inside. I'll paste
that in:

[[[ code('fa4f904887') ]]]

If you have our setup, everything just works. If not, you can 
set this environment variable or create a `phpunit.xml` file 
to override everything.

Let's double-check that this all works:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

## Tests Killed our Database

One *little* bummer is that the tests are using our development database.
Since those create a `weaverryan` user with password `foo`, that still works.
But the cute programmer we created earlier is gone - they've been wiped out,
sent to /dev/null... hate to see that.

## Configuring the test Environment

Symfony has a `test` environment for *just* this reason. So let's use it!
Start by copying `app_dev.php` to `app_test.php`, then change the environment
key from `dev` to `test`. To know if this all works, put a temporary
`die` statement right on top:

[[[ code('4517e45d88') ]]]

We'll setup our tests to hit *this* file instead of `app_dev.php`, which
is being used now because Symfony's `server:run` command sets up the web
server with that as the default.

Once we do that, we can setup the `test` environment to use a different database
name. Open `config.yml` and copy the `doctrine` configuration. Paste it
into `config_test.yml` to override the original. All we really want to
change is `dbname`. I like to just take the real database name and suffix
it with `_test`:

[[[ code('419438bc81') ]]]

Ok, last step. In `phpunit.xml.dist`, add a `/app_test.php` to the end of
the URL. In theory, all our API requests will now hit *this* front controller.

Run the test! This *shouldn't* pass - it should hit that `die`
statement on every endpoint:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

They fail! But not for the reason we wanted:

    Unknown database `symfony_rest_recording_test`

Woops, I forgot to create the new test database. Fix this with 
`doctrine:database:create` in the `test` environment and `doctrine:schema:create`:

```bash
php app/console doctrine:database:create --env=test
php app/console doctrine:schema:create --env=test
```

Try it again:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Huh, it passed. *Not* expected. We should be hitting this `die` statement.
Something weird is going on.

## Debugging Weird/Failing Requests

Go into `ProgrammerControllerTest` to debug this. We *should* be going to
a URL with `app_test.php` at the front, but it *seems* like that's not happening.
Use `$this->printLastRequestUrl()` after making the request:

[[[ code('7d8164e463') ]]]

This is one of the helper functions I wrote - it shows the *true* URL that
Guzzle is using.

Now run the test:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Huh, so there's *not* `app_test.php` in the URL. Ok, so here's the deal.
With Guzzle, if you have this opening slash in the URL, it takes that string
and puts it right after the domain part of your `base_url`. Anything after
that gets run over. We *could* fix this by taking out the opening slash
everywhere - like `api/programmers` - but I just don't like that: it looks
weird.

## Properly Prefixing all URIs

Instead, get rid of the `app_test.php` part in `phpunit.xml.dist`:

[[[ code('11755b5401') ]]]

We'll solve this a different way. When the `Client` is created in `ApiTestCase`,
we have the chance to attach listeners to it. Basically, we can hook into
different points, like right before a request is sent or right after. Actually,
I'm already doing that to keep track of the Client's history for some debugging
stuff.

I'll paste some code, and add a `use` statement for this `BeforeEvent` class:

[[[ code('4819b3285d') ]]]

Ah Guzzle - you're so easy to understand sometimes! So as you can probably
guess, this function is called *before* every request is made. All we do
is look to see if the path starts with `/api`. If it does, prefix that with
`/app_test.php`. This will make every request use that front controller,
without ever needing to think about that in the tests.

Give it another shot:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Errors! Yes - it doesn't see a `programmers` property in the response because
all we have is this crumby die statement text. Now that we know things hit
`app_test.php`, go take that `die` statement out of it. And remove the
`printLastRequestUrl()`. Run the entire test suite:

```bash
phpunit -c app
```

Almost! There's 1 failure! Inside testPOST - we're asserting that the `Location`
header is this string, but now it has the `app_test.php` part in it. That's
a false failure - our code *is* really working. Let's soften that test a bit.
How about replacing `assertEquals()` with `assertStringEndsWith()`. Now
let's see some passing:

```bash
phpunit -c app
```

Yay!
