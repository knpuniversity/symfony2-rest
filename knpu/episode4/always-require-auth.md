# Lock down:Require Authentication Everywhere

The *only* endpoint that requires authentication is `newAction()`. But to use our
API, we want to require authentication to use *any* endpoint related to programmers.

Ok, just add `$this->denyAccessUnlessGranted()` to every controller. OR, use a cool
trick from SensioFrameworkExtraBundle. Give the controller class a doc-block and
give it a new annotation: `@Security`. Auto-complete that to get the `use` statement.
Then, add `"is_granted('ROLE_USER')"`. Now we're requiring a valid user on *every*
endpoint.

If we re-run the tests with:

```bash
./vendor/bin/phpunit tests/AppBundle/Controller/Api/ProgrammerControllerTest.php
```

we should see a *lot* of failures. Fail, fail, fail, fail! Try not to take it personally.
We're *not* sending an Authorization header yet in most tests.

So, now we need to fix that, And we want to fix it with as little work as possible.
Copy the `$token = ` code and delete it. Let's make our lives easy: click into `ApiTestCase`.
Add a new `protected function` called `getAuthorizedHeaders` with two arguments:
a `$username` and an optional array of other `$headers` you want to send on the request.

Paste the `$token = ` ocde here and add a new `Authorization` header that's equal
to `Bearer ` and then the token. Return the whole array of headers.

Now, copy the method name. Oh, and don't forget to actually *use* the `$username`
argument! In `ProgrammerControllerTest`, add a `headers` key set to
`$this->getAuthorizedHeaders('weaverryan')`.

And we just need to repeat this on every single method inside of this test. I'll
look for `$this->client` to find all of these... and do it as fast as I can! By
hooking into Guzzle, we *could* add the `Authorization` header to every request
automatically... but there might be *some* requests where we do *not* want this header.

In fact, at the bottom, we actually test what happens when we don’t send the `Authorization`
header. Skip adding the header here.

With any luck, we should get a bunch of *beautiful* passes.

```bash
./vendor/bin/phpunit tests/AppBundle/Controller/Api/ProgrammerControllerTest.php
```

And we are! Ooh, until we hit the last test! When we *don't* send an Authorization
header to an endpoing that requires authentication... it's *still* returning a 200
status code instead of 401. When we kick out non-authenticated API requests, they
are *still* being redirected to the login page... which is clearly *not* a cool way
for an API to behave.

Let's fix that.
