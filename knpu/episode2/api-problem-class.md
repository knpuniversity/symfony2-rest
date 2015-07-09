# Modeling the Error: ApiProblem Class

Ok, we've got a format for errors - and we're going to use this whenever *anything*
goes wrong - like a 404 error, authentication error or a 500 error. And each time,
this format needs to be *perfectly* consistent.

So instead of creating this `$data` array by hand when things go wrong, let's create
a class that models all this stuff.

## The ApiProblem Class

I actually started this for us. In PhpStorm, I'll switch my view back so I can see
the `resources/` directory at the root. Copy the `ApiProblem.php` file. In `AppBundle`,
create a new `Api` directory and paste the file there:

[[[ code('96b022be23') ]]]

The namespace is already `AppBundle\Api` - so that's perfect. This holds data for
an `application/problem+json` response. It has properties for `type`, `title` and
`statusCode` - these being the three *main* fields from the spec.

And it has a spot for extra fields:

[[[ code('be552bb054') ]]]

If you call `set()`, we can add any extra stuff, like `errors` for validation. And
when we're all done, we'll call the `toArray()` method to get all this back as a
flat, associative array:

[[[ code('1d2352c8f9') ]]]

## Using ApiProblem

Ok, that's looking nice! Let's use this back in `ProgrammerController`. Start with
`$apiProblem = new ApiProblem()`. The status code is 400, the type is `validation_error`
and the title is `There was a validation error`. Knock this onto multiple lines for
readability:

[[[ code('775b5a78fe') ]]]

Get rid of the `$data` variable. To add the extra `errors` field, call `$apiProblem->set()`
and pass it the `errors` string and the `$errors` variable:

[[[ code('b3268fa986') ]]]

The last step is to update `JsonResponse`. Instead of `$data`, use `$apiProblem->toArray()`.
And to avoid duplication, use `$apiProblem->getStatusCode()` instead of 400:

[[[ code('d597a60cbd') ]]]

It's not perfect yet - but that's a lot more dependable. Nothing should change - so
try the tests:

```bash
./bin/phpunit -c app --filter testValidationErrors
```

And yep! We're still green.

But go back and make the test fail somehow - like change the assert for the header.
I want to see the response for myself. Re-run things:

```bash
./bin/phpunit -c app --filter testValidationErrors
```

Scroll up to the dumped response. Yes - we've got the `Content-Type` header, the
`type` and `title` keys, *and* a new `status` field that the spec recommends.

Fix that test. Ok, we're ready for other stuff to go wrong.
