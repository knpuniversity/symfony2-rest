# Test Code Reuse

We'll have a bunch of test classes and they'll all need to create a Guzzle
`Client` with these options. So let's just get organized now.

Create a new `Test` directory in the bundle and a new class called `ApiTestCase`.
This will be a base class for all our API tests. Make *it* extend the normal
`PHPUnit_Framework_TestCase`:

[[[ code('36612134bd') ]]]

Right now, the thing I want to move *out* of each test class is the creation
of the Guzzle `Client`. So copy that code. In `ApiTestCase`, override a method
called `setupBeforeClass()` - it's static. PHPUnit calls this *one* time
at the beginning of running your whole test suite.

Paste the `$client` code here. Because really, even if we run A LOT of tests,
we can probably always use the same Guzzle client. Create a `private static`
property called `$staticClient` and put the `Client` there with `self::$staticClient`.
And give `Client` a proper `use` statement:

[[[ code('9263e01200') ]]]

***TIP
In case you are using Guzzle 6, you would need to use the `base_uri` key instead of `base_url` to configure Guzzle client properly.
***
Cool. So now the `Client` is created once per test suite. Now, create a
`protected $client` property that is *not* static with some nice PHPDoc above
it. Woops - make sure you actually make this `protected`: this is what we'll
use in the sub-classes. Then, override `setup()` and say
`$this->client = self::$staticClient`:

[[[ code('753edbfce9') ]]]

`setupBeforeClass()` will make sure the `Client` is created just once
and `setup()` puts that onto a non-static property, just because I like non-static
things a bit better. Oh, and if we *did* need to do any clean up resetting
of the Client, we could do that in `setup()` or `tearDown()`.

## Extend the Base Class

Back in the actual test class, get rid of the `$client` code and simply reference
`$this->client`. Ooooo, and don't forget to extend `ApiTestCase` like I just
did:

[[[ code('0c1d4558ab') ]]]

Make sure we didn't break anything:

```bash
php bin/phpunit -c app src/AppBundle/Tests/Controller/API/ProgrammerControllerTest.php
```

Hey, still green!
