# ResponseFactory: Centralize Error Responses

In the `EventListener` directory, we created an `ApiExceptionSubscriber` whose job
is to catch all exceptions and turn them into nice API problem responses. And it
already has all of the logic we need to turn an `ApiProblem` object into a proper
response:

[[[ code('0e2f105781') ]]]

Instead of re-doing this in the authenticator, let's centralize and re-use this stuff!
Copy the last ten lines or so out of `ApiExceptionSubscriber`:

[[[ code('91d71cc2a4') ]]]

And in the `Api` directory, create a new class called `ResponseFactory`. Inside,
give this a `public function` called `createResponse()`. We'll pass it the `ApiProblem`
and *it* will turn that into a `JsonResponse`:

[[[ code('c251453760') ]]]

Perfect! Next, go into `services.yml` and register this: how about `api.response_factory`.
Set the class to `AppBundle\Api\ResponseFactory` and leave off the `arguments` key:

[[[ code('b74e8e17c6') ]]]

## Using the new ResponseFactory

We will *definitely* need this inside `ApiExceptionSubscriber`, so add it as a second
argument: `@api.response_factory`:

[[[ code('694f1c081e') ]]]

In the class, add the second constructor argument. I'll use `option`+`enter` to quickly
create that property and set it for me:

[[[ code('bddefa8c67') ]]]

Below, it's very simple: `$response = $this->responseFactory->createResponse()`
and pass it `$apiProblem`:

[[[ code('ad8edfb8c6') ]]]

LOVE it. Let's celebrate by doing the same in the authenticator. Add a third constructor
argument and then create the property and set it:

[[[ code('108c1558dc') ]]]

Down in `start()`, `return $this->responseFactory->createResponse()` and pass it
`$apiProblem`:

[[[ code('bd341a1813') ]]]

Finally, go back to `services.yml` to update the arguments. Just kidding! We're using
autowiring, so it will automatically add the third argument for us:

[[[ code('1580b54e1e') ]]]

If everything went well, we should be able to re-run the test with great success:

```bash
./vendor/bin/phpunit --filter testPOSTTokenInvalidCredentials
```

## detail(s) Make tests Fails

Oh, boy - it failed. Let's see - something is wrong with the `detail` field:

> Error reading property detail from available keys detail**s**.

That sounds like a Ryan mistake! Open up `TokenControllerTest`: the test is looking
for `detail` - with *no* `s`:

[[[ code('f023a01fb4') ]]]

That's correct. Inside `JwtTokenAuthenticator`, change that key to `detail`:

[[[ code('a76c56e369') ]]]

Ok, technically we can call this field whatever we want, but `detail` is kind
of a standard.

Try the test again.

```bash
./vendor/bin/phpunit --filter testPOSTTokenInvalidCredentials
```

That looks perfect. In fact, run our *entire* test suite:

```bash
./vendor/bin/phpunit
```

Hey! We didn't break any of our existing error handling. Awesome!

But there is *one* more case we haven't covered: what happens if somebody sends
a *bad* JSON web token - maybe it's expired. Let's handle that final case next.
