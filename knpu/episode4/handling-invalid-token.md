# Graceful Errors for an Invalid JWT

We already know that if the client *forgets* to send a token, Symfony calls the
`start()` method. But what happens if authentication fails?

## Testing with a bad Token

Let's find out! Copy `testRequiresAuthentication`, paste it, and rename it to
`testBadToken`. In this case, we *will* add a `headers` key and we *will* send an
`Authorization` header... but set to `Bearer WRONG`.

If this happens, we definitely want a 401 status code and - like always - an
`application/problem+json` response header. Let's *just* look for these two things
for now.

## How Authentication Fails

When JWT authentication fails, what handles that? Well, `onAuthenticationFailure`
of course! The `getUser()` method *must* return a `User` object. If it doesn't,
then `onAuthenticationFailure` is called. In our case, there are two possible reasons:
the token might be corrupted or expired *or* - somehow -  the decoded username doesn't
exist in our database. In both cases, we are *not* returning a User object, and this
triggers `onAuthenticationFailure`.

To start, just return a new `Response` that says `Hello`, but with the proper 401
status code. Copy the `testBadToken` method name and give it a try!

```bash
./vendor/bin/phpunit --filter testBadToken
```

## ApiProblem on Failure

It *almost* works - that's a good start. It proves our code in `onAuthenticationFailure`
is handling things. Now, let's setup a proper API problem response, just like we
did before: `$apiProblem = new ApiProblem` with a 401 status code. Then, use
`$apiProblem->set()` to add a `detail` field. And in this case, we *always* have
an `AuthenticationException` that can hint what went wrong. Use its `getMessageKey()`
method.

Oh, and by the way - if you want, you can send this through the `translator` service
and translate into multiple languages.

Finish this with `return $this–>responseFactory->createResponse()` to turn the
`$apiProblem` into a nice JSON response.

That's it! We did all the hard work earlier.

I want to actually *see* how this response looks. So, add a `$this->debugResponse()`
at the end of `testBadToken`. Now, re-run the test!

```bash
./vendor/bin/phpunit --filter testBadToken
```

Check that out - it's beautiful! It has all the fields it needs, including `detail`,
which is set to `Invalid token`.

## Controlling Error Message

That text is coming from *our* code, when we throw the `CustomUserMessageAuthenticationException`.
The text - `Invalid token` - becomes the "message key" and this exception is passed
to `onAuthenticationFailure`.

This gives you complete control over how your errors look.
