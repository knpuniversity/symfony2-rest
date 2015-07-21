# Creating the Invalid JSON ApiProblem, and then...

The nice API Problem JSON format always has a `type` key, so let's at least start
looking for that in the response. Use `$this->asserter()->assertResponsePropertyEquals()`
and pass it the `$response` and `type` as the key. For the value - how about
`invalid_body_format`. That's our *second* special error "type" - the first was
`validation_error`.

[[[ code('feb17eecfd') ]]]

This should get our test to fail again:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Gooood - we're still returning the exception HTML.

## Creating the ApiProblem

Let's fix this just like we did for validation errors: by creating an `ApiProblem`
object. First, we need a new `type` constant. Create a second constant called
`TYPE_INVALID_REQUEST_BODY_FORMAT` and set that to the string from our test: `invalid_body_format`.
Setup a title for this too: how about "Invalid JSON format sent". And I better fix
my syntax error:

[[[ code('af7772f9e6') ]]]

Back in `ProgrammerController`, we can get to work: `$apiProblem = new ApiProblem()`.
Pass it the 400 status code and the type: `ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT`:

[[[ code('bfca560acc') ]]]

## We're stuck

Gosh, everything is going really well! And now we're stuck. For validation, we took
the `ApiProblem`, turned it into a Response and returned it. But inside `processForm()`,
we're big trouble: the return value of this method isn't being used. We can
only *throw* an exception to stop the flow. And while we *can* control the status
code of an exception, the response body that an `HttpException` generates is still
an HTML error page.
