# The ResponseAsserter!

In *every* test, we're going to decode the JSON response and then assert
some stuff - like "does the nickname property exist?" or "is it equal to UnitTester?"

Find the `resources` directory at the root of your project. I'm switching
my PhpStorm mode up here temporarily, because I marked this directory as
"excluded" so that Storm wouldn't try to autocomplete from stuff in it.
See that `ResponseAsserter.php` file? Yea, copy that - it's good stuff.

Paste it into the `Test` directory right next to `ApiTestCase`. And now I'll
re-hide that `resources` folder in PhpStorm.

Hello `ResponseAsserter`! This class is really good at reading properties
off of a JSON response:

[[[ code('213705aed5') ]]]

We won't read through this now, but you should. It uses the same `PropertyAccess`
component internally - and we'll use its superpowers through this.

## Setting things up in ApiTestCase

To use this in `ApiTestCase`, create a new private property called `$responseAsserter`:

[[[ code('8a5d2c4f5d') ]]]

And then *way* down at the bottom - make a `protected function asserter()`.
We'll use the property to avoid making multiple asserters. So, if
`$this->responseAsserter === null` then set that to a `new ResponseAsserter()`.
Finish by returning it:

[[[ code('5466215646') ]]]

## Assert!

Now let's use this! Instead of having Guzzle decode the JSON for us, we can
just say `$this->asserter()->responsePropertiesExist()` and pass it the
`$response` we want it to look at and the array of properties that should
exist in its JSON:

[[[ code('3b193ec404') ]]]

That gets rid of a nice block of code. Inside the new function, it just loops
over each property and reads their value using the `PropertyAccess` component.
And it *is* still just using `json_decode` internally. It's just an easier
way to look into the JSON response.

Since we're responsible coders, let's assert that it all works:

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Excellent! Let's add one more - an assert that the `nickname` is set to
`UnitTester`. Use `assertResponsePropertyEquals()` - always pass the `$response`
first. Then, `nickname` and it should equal `UnitTester`:

TODO CODE

Run that!

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

And *that* passes. Nothing scares me more than when things are green on the first try, 
... well that and snakes on a plane. So, let's assert `UnitTester2` and see it fail.

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Phew, ok good!

    Property "nickname": Expected "UnitTester2" but response was "UnitTester"

And like *all* failures, it prints out the raw response above this.

## Testing /api/programmers

Our testing setup is, well, pretty sweet. So testing the GET collection endpoint
should be easy. Create a `testGETProgrammersCollection()` method. Grab the
`createProgrammer()` code from above, but paste it twice to create a new `CowboyCoder`:

CODE TODO

Now grab the lines that makes the request and asserts the status code. Update
the URL to just `/api/programmers`. No assertions yet, but let's make sure
it doesn't blow up:

```bash
phpunit -c app src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Great! Ok, so what do we want to assert? I don't know what do you want to assert?
Think about how the endpoint works: we're returning an associative array 
with a `programmers` key and *that* actually holds the collection of programmers:

CODE TODO

Let's first assert that there's a `programmers` key in the response and that
it's an array. Use `$this->asserter()->assertResponsePropertyIsArray()`:
pass it the `$response` and the property: `programmers`. Next, let's assert
that there are *two* things on this array. There's a method for that called
`assertResponsePropertyCount()` - pass it the `$response`, `programmers`
and the number 2:

CODE TODO

Now let's run this - but copy the method name first. On the command line,
before the filename, add `--filter` then paste the method name to *just*
run this test:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

Yep - one little dot and the PHPUnit gnomes are pleased.

## Deep Assertions with Property Path

Let's go further. We know the `programmers` property will have 2 items in
it: the 0 index should be the `UnitTester` data and the 1 index should be
the `CowboyCoder` data. Copy the `assertResponsePropertyEquals()` method
and paste it here. But instead of just `nickname`, use `programmers[1].nickname`.
And this should be `CowboyCoder`:

TODO CODE

And that's the super-power of the PropertyAccess component: it lets you walk
down through the response data. This is really fun, give this a try:

```bash
phpunit -c app --filter testGETProgrammersCollection src/AppBundle/Tests/Controller/Api/ProgrammerControllerTest.php
```

We're still passing. If you change that to `CowboyCoder2`, we get that really
clear failure message and the dumped JSON response right above it. We're
dangerous. Change that test back so it passes.
