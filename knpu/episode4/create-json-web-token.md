# Create a Shiny JSON Web Token

Create a new `TokenController` in the `Api` directory:

[[[ code('6f9e8d3317') ]]]

Make this extend the same `BaseController` from our project and let's get to work!

First create a `public function newTokenAction()`. Add the `@Route` above and let it
autocomplete so that the `use` statement is added for the annotation. Set the URL
to `/api/tokens`. Heck, let's get crazy and also add `@Method`: we only want this
route to match for POST requests:

[[[ code('5a89451779') ]]]

To start, don't get too fancy: just return a new `Response` from `HttpFoundation` with
`TOKEN!`:

[[[ code('2d5ae4aa61') ]]]

Got it! That won't make our test pass, but it is an improvement. Re-run it:

```bash
./vendor/bin/phpunit --filter testPOSTCreateToken
```

Still failing - but now it has the 200 status code.

## Checking the Username and Password

Head back to `TokenController`. Here's the process:

1. Check that the username and password are correct.
2. Generate a JSON web token.
3. Send it back to the client.
4. High-five everyone at your office. I can't wait to get to that step.

Type-hint a new argument with `Request` to get the request object:

[[[ code('a748f6a67e') ]]]

Next, query for a User object with the normal `$user = $this->getDoctrine()->getRepository('AppBundle:User')`
and `findOneBy(['username' => ''])`. Get the HTTP Basic username string with `$request->getUser()`:

[[[ code('d86ce60b81') ]]]

And what if we can't find a user? Throw a `$this->createNotFoundException()`:

[[[ code('3b76afae0c') ]]]

If you wanted to *hide* the fact that the username was wrong, you can throw
a `BadCredentialsException` instead - you'll see me do that in a second.

Checking the password is *easy*: `$isValid = $this->get('security.password_encoder')`
`->isPasswordValid()`. Pass it the `$user` object and the raw HTTP Basic password
string: `$request->getPassword()`:

[[[ code('d5cc47b689') ]]]

If this is *not* valid, throw a new `BadCredentialsException`. We're going to talk
a lot more later about properly handling errors so that we can control the exact
JSON returned. But for now, this will at least kick the user out.

Ok, ready to finally generate that JSON web token? Create a `$token` variable and
set it to `$this->get('lexik_jwt_authentication.encoder')->encode()` and pass that
*any* array of information you want to store in the token. Let's store
`['username' => $user->getUsername()]` so we know *who* this token belongs to:

[[[ code('5b2a065f04') ]]]

***TIP
Don't forget to pass an `exp` key to the token, otherwise the token will *never*
expire! We forgot to do this in the video!
***

But you can store anything here, like roles, user information, some poetry - whatever!

And that's it! This is a string, so return a new `JsonResponse` with a token field
set to `$token`:

[[[ code('c32740cf59') ]]]

That's it, that's everything. Run the test!

```bash
./vendor/bin/phpunit --filter testPOSTCreateToken
```

It passes! Now, make sure a *bad* password fails. Duplicate this method:

[[[ code('73b22502cc') ]]]

and rename it to `testPOSTTokenInvalidCredentials()`. But now, we'll lie and pretend
my password is `IH8Pizza`... even though we know that `I<3Pizza`:

[[[ code('b93d910030') ]]]

Check for a 401 status code. Copy the method name and go run that test:

```bash
./vendor/bin/phpunit --filter testPOSTTokenInvalidCredentials
```

It should pass... but it doesn't! Interesting. Look at this: it definitely doesn't
return the token... it redirected us to `/login`. We *are* getting kicked out of
the controller, but this is *not* how we want our API error responses to work.
We'll fix this a bit later.
