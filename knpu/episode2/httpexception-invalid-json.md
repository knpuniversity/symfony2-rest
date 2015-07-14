# The Important HttpException (+ handling Invalid JSON)

What do you think would happen if we POST'ed some badly-formatted JSON to an endpoint?
Because, I'm not really sure - but I bet the error wouldn't be too obvious to the
client.

## The invalidJson Test

Let's add a test for this and think about how we *want* our API to act if someone
mucks up their JSON. Copy `testValidationErrors()` - it should be pretty similar.
Name the new method `testInvalidJSON()`:

[[[ code('b6732a755c') ]]]

And we can't use this `$data` array anymore, `json_encode` is too good at creating
*valid* JSON. Overachiever. Replace it with an `$invalidJson` variable - we'll have
to create really bad JSON ourselves. Let's see here, start with one piece of valid
JSON, remove a comma and liven things up with a hanging quotation mark, and that
oughta do it! Now pass `$invalidJson` as the request body:

[[[ code('45acca958d') ]]]

Ok, time to think about how we want the response to look. The 400 status code is
good. Invalid JSON is the client's fault, and any status code starting with 4 is
for when *they* mess up. You could also use 422 - Unprocessable Entity - if you want
to enhance your nerdery. But, nobody is going to notice.

And since we're curious about how our API *currently* handles invalid JSON, use
`$this->debugResponse()` right above the assert:

[[[ code('96c56bc3b2') ]]]

Copy the test name and give it a try:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Cool - the test passes with a 400 response, but the error isn't about having invalid
JSON. Instead, it looks like we're missing our nickname. Ok, so let's add a nickname:

[[[ code('ed97114737') ]]]

And try the test again:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

And we *still* fail because the `nickname` field is missing. So apparently, if we
send invalid JSON, it acts like we're sending nothing. So good luck to any future
API client trying to debug this.

## Handling Invalid JSON

In `ProgrammerController`, search for `json_decode` - you'll find it in `processForm()`:

[[[ code('8138903d01') ]]]

If the `$body` has a bad format, then `$data` will be `null`. Add an `if` to test
for that: if `null === $data` then we need to return that 400 status code:

[[[ code('c43d069f1e') ]]]

## The HttpException(Interface)

But wait! There's a huge problem! When `processForm()` is called, its return value
isn't used:

[[[ code('a819e652bb') ]]]

So if we return a Response from `processForm()`... good for us! Nobody will actually
do anything with that: `newAction` will continue on like normal. The only way we
can break the flow from inside `processForm()` is by throwing an exception. But as
you're probably thinking, if you throw an exception in Symfony, that turns into a
*500* error. We need a 400 error.

And it turns out, that's totally possible - and it's a really important concept for API's.
First, I just said that throwing an exception causes a 500 error in Symfony.
That's just not the whole story. Throw a new `HttpException` from HttpKernel. It
has 2 arguments: the status code - 400 - and a message - just "Invalid JSON" for now.
Don't worry *yet* about returning our nice API problem JSON:

[[[ code('a2d9295db7') ]]]

So here's the truth about exceptions: any exception will turn into a 500 error, *unless*
that exception implements the `HttpExceptionInterface`:

[[[ code('6525fef246') ]]]

It has two functions: `getStatusCode()` and `getHeaders()`. The `HttpException` class
we're throwing implements this. That means we can throw this from *anywhere* in our
code, stop the flow, but control the status code of the response. Symfony ships with
a bunch of convenience sub-classes for common status codes, like `ConflictHttpException`,
which automatically gives you a 409 status code. All of those classes are optional:
you could use `HttpException` for everything.

Ok, back to reality. Since we're throwing this, the response should still be 400,
so the test should still pass. But, instead of getting back a validation error, we
should get back our simple "Invalid JSON" text message:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Yep! It passes and prints out "Invalid JSON". The "There was an error" part is from
my test helper - but the red text below is the actual response. The point is that
we *are* handling invalid JSON now, but we're not sending back the awesome API Problem
JSON format yet.
