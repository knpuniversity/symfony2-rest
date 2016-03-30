# Send the Token in the Request

We already added a `denyAccessUnlessGranted()` line to `ProgrammerController::newAction()`.
That means this endpoint is broken: we don't have an API authentication system hooked
up yet.

Open up `ProgrammerControllerTest` and find `testPOST`: the test for this endpoint.
Rename this to `testPOSTProgrammerWorks` - this will make its name unique enough
that we can run it alone. Copy that name and run it:

```bash
./vendor/bin/phpunit --filter testPOSTProgrammerWorks
```

Instead of the 201, we get a 200 status code after being redirected to `/login`.
I know we don't have our security system hooked up yet, but pretend that it *is*
hooked up and working nicely. How can we update the test to *send* a token?

## Sending a Token in the Test

Well, first, we'll need to create a valid token. Do that the same way we just did in
the controller: `$token = $this->getService()` - which is just a shortcut we made
to fetch a service from the container - and grab the `lexik_jwt_authentication.encoder`
service. Finally, call `encode()` and pass it `'username' => 'weaverryan'`.

And we have a token! Now, how do we send it to the server? Well, it's our API, so we
can do whatever the heck we want! We can set it as a query string or attach it on
a header. The most common way is to set it on a header called `Authorization`.
Add a `headers` key to the Guzzle call with one header called `Authorization`. Set
its value to the word `Bearer`, a space, and then the `$token.`

Weird as it might look, this is a really standard way to send a token to an API.
If we re-run the test now, it of course still fails. But we're finally ready to
create an authentication system that looks for this token and authenticates our user.
