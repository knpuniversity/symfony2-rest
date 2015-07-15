# ApiProblemException

The `ApiProblem` object knows everything about how the response should look, including
the status code and the response body information. So, it'd be great to have an easy way to
convert this into a Response.

But, I want to go further. Sometimes, having a Response isn't enough. Like in `processForm()`:
since nothing uses its return value. So the only way to break the flow is by throwing an
exception.

Here's the goal: create a special exception class, pass it the `ApiProblem` object,
and then have some central layer convert that into our beautiful API problem JSON
formatted response. So whenever something goes wrong, we'll just need to create the
`ApiProblem` object and then throw this special exception. That'll be it, in *any*
situation.

## Create the ApiProblemException

In the `Api` directory, create a new class called `ApiProblemException`. Make this
extend `HttpException` - because I like that ability to set the status code on this:

[[[ code('5a683e4884') ]]]

Next, we need to be able to attach an `ApiProblem` object to this exception class,
so that we have access to it later when we handle all of this. Let's pass this via
the constructor. Use `cmd+n` - or go to the "Generate" menu at the top - and override
the `__construct` method. Now, add `ApiProblem $apiProblem` as the first argument.
Also create an `$apiProblem` property and set this there:

[[[ code('8ccd860bc2') ]]]

This won't do *anything* special yet: this is still just an `HttpException` that
happens to have an `ApiProblem` attached to it.

Back in `ProgrammerController`, we can start using this. Throw a new `ApiProblemException`.
Pass it `$apiProblem` as the first argument and 400 next:

[[[ code('6d2d80e428') ]]]

Run the test:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

It still acts like before: with a 400 status code, and now an exception with no message.

## Simplifying the ApiProblemException Constructor

Before we handle this, we can make one minor improvement. Remove the `$statusCode`
and `$message` arguments because we can get those from the `ApiProblem` itself. Replace
that with `$status = $apiProblem->getStatus()`. And I just realized I messed up my
first line - make sure you have `$this->apiProblem = $apiProblem`. Also add
`$message = $apiProblem->getTitle()`:

[[[ code('e47d7d05de') ]]]

Hey wait! `ApiProblem` doesn't have a `getTitle()` method yet. Ok, let's go add one.
Use the Generate menu again, select "Getters" and choose `title`:

[[[ code('e196289cce') ]]]

In `ProgrammerController`, simplify this:

[[[ code('4a809af6c7') ]]]

It'll figure out the status code and message for us.

```bash
./bin/phpunit -c app --filter testInvalidJson
```

The exception class is perfect - we just need to add that central layer that'll
convert this into the beautiful API Problem JSON response. Instead of this HTML
stuff.
