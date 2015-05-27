# Using a Test Database

We're using the built-in PHP web server and it's running on port 8000. We
also have that hardcoded at the top of `ApiTestCase`: when the Client is
created, it *always* goes to `localhost:8000`. Bummer! All of our co-workers
will need to have the exact same setup.

Let's make this configurable - create a new variable `$baseUrl` and set it
to an environment variable called `TEST_BASE_URL` - I'm making that name
up. Use this for the `base_url` option:

[[[ code('8ed091385d') ]]]

There are endless ways to set environment variables. But we want to at least
give this a default value. Open up `app/phpunit.xml.dist`. Get rid of those
comments - we want a `php` element with an `env` element inside. I'll paste
that in:

[[[ code('fa4f904887') ]]]

If you have our setup, everything just works. If not, set this environment
variable or create a `phpunit.xml` file to override everything.

Let's double-check that this all works:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

## Tests Killed our Database

One *little* bummer is that the tests are using our development database.
Since those create a `weaverryan` user with password `foo`, that still works.
But the cute programmer we created earlier is gone. That's a bumer





