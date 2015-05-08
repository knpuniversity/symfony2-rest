# Add a Test!

This `testing.php` file is basically already a test... except it's missing
the most important part: the ability to start shouting when something
breaks.

To test our API, we'll use PHPUnit! Yes! Awesome! I'm excited because even
though PHPUnit isn't the most exciting tool, it's solid - and we're going
to do some cool stuff with our tests.

**TIP** In our other [REST tutorial](http://knpuniversity.com/screencast/rest),
we tested with Behat. Both are great, and really the same under the surface.

## Create that Test

Create a `Tests` directory inside `AppBundle`. Now mimic your directory structure.
So, add a `Controller` directory, then an `API` directory, and finish it with
a new PHPUnit test class for `ProgrammerController`. Be a good programmer
and fill in the right namespace. All these directories: technically unnecessary.
But now we've got a sane setup.

Of course, we'll test our POST endpoint - so create `public function testPOST()`:

[[[ code('81b22edf4a') ]]]

I'm being inconsistent - the controller is `newAction`, but this method is
`testPOST` - it would be cool to have these match - maybe even with a mixture
of the two - like `postNewAction()`.

Anyways, let's go steal our first request code from `testing.php` and paste
it into `testPOST`:

[[[ code('7cff58981b') ]]]

Ok cool. No asserts yet - but let's see if it blows up. I already installed
PHPUnit into this project, so run `php bin/phpunit -c app` then the path to
the test:

```bash
php bin/phunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Pretty green! No assertions yet, but also no explosions. Solid start team!

## Be Assertive

Ok, what should we assert? Always start with the status code - `$this->assertEquals()`
that the expected `201` equals `$response->getStatusCode()`:

[[[ code('845afe22ef') ]]]

Second: what response header should we send back whenever we create a resource?
Location! Right now, just `assertTrue` that `$response->hasHeader('Location')`.
Soon, we'll assert the actual value.

[[[ code('5b3509f2df') ]]]

And to put a bow on things, let's `json_decode` the response body into an
array, and just assert that is *has* a `nickname` key with `assertArrayHasKey`,
with `nickname` and `$data`:

[[[ code('77b4b155e7') ]]]

In a second, we'll assert the actual value. It's not a super-tight test yet,
but let's give it a shot:

```bash
php bin/phunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Yes! This time we deserve that green.
