# Lock down: Require Authentication Everywhere

The *only* endpoint that requires authentication is `newAction()`. But to use our
API, we want to require authentication to use *any* endpoint related to programmers.

## Using @Security

Ok, just add `$this->denyAccessUnlessGranted()` to every method. OR, use a cool
trick from `SensioFrameworkExtraBundle`. Give the controller class a doc-block and
a new annotation: `@Security`. Auto-complete that to get the `use` statement.
Then, add `"is_granted('ROLE_USER')"`:

[[[ code('768db54713') ]]]

Now we're requiring a valid user on *every* endpoint.

Re-run all of the programmer tests by pointing to the file.

```bash
./vendor/bin/phpunit tests/AppBundle/Controller/Api/ProgrammerControllerTest.php
```

We should see a *lot* of failures. Fail, fail, fail, fail! Don't take it personally.
We're *not* sending an Authorization header yet in most tests.

## Sending the Authorization Header Everywhere

Let's fix that with as little work as possible. Copy the `$token = ` code and delete it:

[[[ code('b00af1ffb7') ]]]

Click into `ApiTestCase` and add a new `protected function` called `getAuthorizedHeaders()`
with two arguments: a `$username` and an optional array of other `$headers` you want
to send on the request:

[[[ code('2a901fff41') ]]]

Paste the `$token = ` code here and add a new `Authorization` header that's equal
to `Bearer ` and then the token. Return the entire array of headers:

[[[ code('66806f90d1') ]]]

Now, copy the method name. Oh, and don't forget to actually *use* the `$username`
argument! In `ProgrammerControllerTest`, add a `headers` key set to
`$this->getAuthorizedHeaders('weaverryan')`:

[[[ code('bdff2546bd') ]]]

And we just need to repeat this on every single method inside of this test. I'll
look for `$this->client` to find these... and do it as fast as I can!

[[[ code('8eef88bc21') ]]]

By hooking into Guzzle, we *could* add the `Authorization` header to every request
automatically... but there might be *some* requests where we do *not* want this header.

In fact, at the bottom, we actually test what happens when we don’t send the `Authorization`
header. Skip adding the header here:

[[[ code('741da48fc5') ]]]

With any luck, we should get a bunch of *beautiful* passes.

```bash
./vendor/bin/phpunit tests/AppBundle/Controller/Api/ProgrammerControllerTest.php
```

And we do! Ooh, until we hit the last test! When we *don't* send an Authorization
header to an endpoint that requires authentication... it's *still* returning a 200
status code instead of 401. When we kick out non-authenticated API requests, they
are *still* being redirected to the login page... which is clearly *not* a cool way
for an API to behave.

Time to fix that.
