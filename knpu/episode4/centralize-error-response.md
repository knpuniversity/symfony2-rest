# Centralize the Error Response

In the `EventListener` directory, we created an `APIExceptionSubscriber` whose job
is to catch all exceptions and turn them into nice API problem responses. And it
already has all of the logic we need to turn an `ApiProblem` object into a proper
response.

Instead of re-doing this in the authenticator, let's centralize and re-use this stuff!
Copy the last ten lines or so out of `ApiExceptionSubscriber`. And in the `Api` directory,
create a new class called `ResponseFactory`. Inside, give this a `public function`
called `createResponse()`. We'll pass it the `ApiProblem` and *it* will turn that
into a `JsonResponse`.

Perfect! Next, go into `services.yml` and register this: how about `api.response_factory`.
Set the class to `AppBundle\Api\ResponseFactory` and leave off the `arguments` key.

## Using the new ResponseFactory

We will *definitely* need this inside `ApiExceptionSubscriber`, so add it as a second
argument: `@api.response_factory`.

In the class, add the second constructor argument. I'll use option+enter to quickly
create that property and set it for me. Below, it's very simple:
`$response = $this->responseFactory->createResponse()` and pass it `$apiProblem`.

LOVE it. Let's celebrate by doing the same in the authenticator. Add a third constructor
argument and then create the property and set it.

Down in `start()`, instead of creating the response by hand,
`return $this->responseFactory->createResponse()` and pass it `$apiProblem`. Finally,
go back to `services.yml` to update the arguments. Just kidding! We're using autowiring,
so it will automatically add the third argument for us.

If everything went well, we shoudl be able to re-run the test with great success:

```bash
./vendor/bin/phpunit -c --filter testPOSTTokenInvalidCredentials
```

## detail(s) Make tests Fails

Oh, boy - it failed. Let's see - something is wrong with the `detail` field:

> Error reading property detail from available keys details.

That sounds like a Ryan mistake! Open up `TokenControllerTest`: the test is looking
for `detail` - with *no* `s`. That's correct. Inside `JWTTokenAuthenticator`, change
that key to `detail`. Ok, techncially we can call this field whatever we want, but
`detail` is kind of a standard.

Try the test again.

```bash
./vendor/bin/phpunit -c --filter testPOSTTokenInvalidCredentials
```

That looks perfect. In fact, run our *entire* test suite:

```bash
./vendor/bin/phpunit
```

Hey! We didn't break any of our existing error handling. Awesome!

But there is *one* more case we haven't covered: what happens if somebody sends
a *bad* JSON web token - maybe it's expired. Let's handle that next.
