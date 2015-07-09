# Keeping Problem types Consistent

Look back at the `title` field in the spec:

    A short, human-readable summary of the problem type. It SHOULD NOT
    change from occurrence to occurrence of the problem, except if you're
    translating this.

In human terms, this means that *every* time we have a `validation_error` type,
the title should be exactly `There was a validation error`. So when we're validating
in other places in the future, both of the `type` *and* the `title` need to be
*exactly* the same. Otherwise, we're making our client's life hard with our gross
inconsistencies.

Because `validation_error` is now a "special string" to us, I think this is great
spot for a constant. In `ApiProblem`, add a constant called `TYPE_VALIDATION_ERROR`
that's set to the string:

[[[ code('61ac9e472e') ]]]

Ok, use that back in the controller: `ApiProblem::TYPE_VALIDATION_ERROR`:

[[[ code('78b31e7b1b') ]]]

Ok, that's better. Next, I also don't want to mess up the title. Heck, I don't even
want to have to *write* the title anywhere - can't it be guessed based on the type?

I think so - let's just create a map from the type, to its associated title. In
`ApiProblem`, add a `private static $titles` property. Let's make it an associative
array map: from `TYPE_VALIDATION_ERROR` to the message: `There was a validation error`:

[[[ code('9771989477') ]]]

In `__construct()`, let's kill the `$title` argument completely. Instead - just use
`self::$titles` and look it up with `$type`:

[[[ code('89fb114e18') ]]]

And we should code defensively, in case we mess something up later. Check
`if (!isset(self::$titles[$type]))` and throw a huge `Exception` message to our future
selves. How about, "Hey - buddy, use your head!". Or, more helpfully:
"No title for type" and pass in the value.

[[[ code('6f1005c96c') ]]]

Now we can pop up the last argument in the controller:

[[[ code('b6732a755c') ]]]

Ok future me, good lucking screwing up our API problem responses in the future. This
is easy to use, and hard to break. Time to re-run the test:

```bash
./bin/phpunit -c app --filter testValidationErrors
```

Great! Let's keep hardening our API.
