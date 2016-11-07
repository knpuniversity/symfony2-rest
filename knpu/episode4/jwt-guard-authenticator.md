# JWT Guard Authenticator (Part 1)

To create our token authentication system, we'll use Guard.

Guard is part of Symfony's core security system and makes setting up custom auth
so easy it's actually fun.

## Creating the Authenticator

In `AppBundle`, create a new `Security` directory. Inside add a new class: `JwtTokenAuthenticator`:

[[[ code('648e6c93b8') ]]]

Every authenticator starts the same way: extend `AbstractGuardAuthenticator`. Now,
all we need to do is fill in the logic for some abstract methods. To get us started
quickly, go to the "Code"->"Generate" menu - `command`+`N` on a Mac - and select
"Implement Methods". Select the ones under Guard:

[[[ code('5b3b199654') ]]]

***TIP
Version 2 of LexikJWTAuthenticationBundle comes with an authenticator that's
based off of the one we're about to build. Feel free to use it instead of
building your own... once you learn how it works.
***

Now, do that *one* more time and also select the `start()` method. That'll put `start()`
on the bottom, which will be more natural:

[[[ code('1d609b20c0') ]]]

If this is your first Guard authenticator... welcome to party! The process is easy:
we'll walk through each method and just fill in the logic. But if you want to know
more - check out the [Symfony security course][1].

## getCredentials()

First: `getCredentials()`. Our job is to read the `Authorization` header and return
the token - if any - that's being passed. To help with this, we can use an object
from the JWT bundle we installed earlier: `$extractor = new AuthorizationHeaderTokenExtractor()`.
Pass it `Bearer` - the prefix we're expecting before the actual token - and `Authorization`,
the header to look on:

[[[ code('b89e52ae9c') ]]]

Grab the token with `$token = $extractor–>extract()` and pass it the `$request`:

[[[ code('0676a0283e') ]]]

If there is *no* token, return `null`:

[[[ code('ef27304393') ]]]

This will cause authentication to stop. Not *fail*, just stop trying to authenticate
the user via this method.

If there *is* a token, return it!

[[[ code('bf0c449a59') ]]]

## getUser()

Next, Symfony will call `getUser()` and pass this token string as the `$credentials`
argument. Our job here is to use that token to find the user it relates to.

And this is where JSON web tokens really shine. Because if we simply decode the token, it
will *contain* the username. Then, we can just look it up in the database.

To do this, we'll need two services. On top of the class, add a `__construct()`
method so we can inject these. First, we need the lexik encoder service. Go back
to your terminal and run:

```bash
./bin/console debug:container lexik
```

Select the `lexik_jwt_authentication.encoder` service. Ah, this is just an alias
for the first service - `lexik_jwt_authentication.jwt_encoder`. And this is an instance
of `JWTEncoder`. Back in the authenticator, use this as the type-hint. Or wait,
since it looks like there's an interface this probably implements, you can use
`JWTEncoderInterface` instead. Give this one more argument: `EntityManager $em`:

[[[ code('c8c7a1588e') ]]]

I'll use a shortcut - `option`+`enter` on a Mac - to initialize these fields:

[[[ code('e9324cb91f') ]]]

This created the two properties and set them for me. Nice!

Head back down to `getUser()`. First: decode the token. To do that,
`$data = $this–>jwtEncoder->decode()` and pass it `$credentials` - that's our token
string:

[[[ code('88f7138311') ]]]

That's it! `$data` is now an array of whatever information we originally put into
the token. Fundamentally, this works just like a normal `json_decode`, except that
the library is also checking to make sure that the contents of our token weren't
changed. It does this by using our *private* key. This guarantees that nobody has
changed the username to some *other* username because they're a jerk. Encryption
is amazing.

It also checks the token's expiration: our tokens last 1 hour because that's what we
setup in `config.yml`:

[[[ code('143a29ada6') ]]]

So, `if ($data === false)`, then we know that there's a problem with the token. If
there is, throw a `new CustomUserMessageAuthenticationException()` with `Invalid token`:

[[[ code('d3952ece00') ]]]

***TIP
In version 2 of the bundle, you should instead use a try-catch around this line:

```php
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
// ...

public function getUser($credentials, UserProviderInterface $userProvider)
{
    try {
        $data = $this->jwtEncoder->decode($credentials);
    } catch (JWTDecodeFailureException $e) {
        // if you want to, use can use $e->getReason() to find out which of the 3 possible things went wrong
        // and tweak the message accordingly
        // https://github.com/lexik/LexikJWTAuthenticationBundle/blob/05e15967f4dab94c8a75b275692d928a2fbf6d18/Exception/JWTDecodeFailureException.php

        throw new CustomUserMessageAuthenticationException('Invalid Token');
    }

    // ...
}
```
***

We'll talk about what that does in a second.

But if everything is good, get the username with `$username = $data['username']`:

[[[ code('3374521794') ]]]

Then, query for and return the user with
`return $this–>em–>getRepository('AppBundle:User')–>findOneBy()` passing
`username` set to `$username`:

[[[ code('14a51a5246') ]]]

## checkCredentials()

If the user is not found, this will return `null` and authentication will fail. But
if a user *is* found, then Symfony finally calls `checkCredentials()`. Just return
`true`:

[[[ code('bd49c6907f') ]]]

There's no password or anything else we need to check at this point.

And that's it for the important stuff!

## Skip Everything Else (for now)

Skip `onAuthenticationFailure()` for now. And for `onAuthenticationSuccess()`,
purposefully do nothing:

[[[ code('298b0f83a5') ]]]

We want the authenticated request to continue to the controller so we can do
our normal work.

In `supportsRememberMe()` - this doesn't apply to us - so return `false`:

[[[ code('17cfd5cb2f') ]]]

And keep `start()` blank for another minute. With just `getCredentials()` and `getUser()`
filled in, our authenticator is ready to go. Let's hook it up!


[1]: https://knpuniversity.com/screencast/symfony-security
