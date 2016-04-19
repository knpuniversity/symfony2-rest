# JWT Guard Authenticator (Part 1)

To create our token authentication system, we'll use Guard. On guard!

Guard is part of Symfony's core security system and makes setting up custom auth
so easy it's actually fun. 

## Creating the Authenticator

In AppBundle, create a new `Security` directory. Inside add a new class: `JWTTokenAuthenticator`.

Every authenticator starts the same way: extend `AbstractGuardAuthenticator`. Now,
all we need to do is fill in the logic for some abstract methods. To get us started
quickly, go to the Code->Generate menu - command+N on a Mac - and select
"Implement Methods". Select the ones under Guard. Now, do that *one* more time and
also select the `start()` method. That'll put `start()` on the bottom, which will
be more natural.

If this is your first Guard authenticator... welcome! The process is pretty easy:
we'll walk through each method and just fill in the logic. But if you want to know
more - check out the Symfony security course.

## getCredentials()

First: `getCredentials()`. Our job is to read the `Authorization` header and return
the token - if any - that's being passed. To help with this, we can use an object
from the JWT bundle we installed later: `$extractor = new AuthorizationHeaderTokenExtractor()`.
Pass it `Bearer` - the prefix we're expecing before the actual token - and `Authorization`,
the header to look on.

Grab the token with `$token = $extractor–>extract()` and pass it the `$request`.
If there is *no* token, return `null`. This will cause authentication to stop. Not
*fail*, just stop trying to authenticate the user via this method.

***TIP
In Symfony 3.1, there is a new `supports()` method and *its* job is to decide whether
or not authentication should continue. It's a very minor difference - just watch
out for it!
***

## getUser()

If there *is* a token, return it! Next, Symfony will call `getUser()` and pass this
token string as the `$credentials` argument. Our job is to use that token to find
the user it relates to.

And this is where JSON web tokens shine. Because if we simple decode the token, it
will *contain* the username. Then, we can just look it up in the database.

To do this, we'll need two services. On top of the class, add a `__construct()`
method so we can inject tehse. First, we need the lexik encoder service. Go back
to your terminal and run:

```bash
bin/console debug:container lexik
```

Select the `lexik_jwt_authentication.encoder` service. Ah, this is just an alias
for the first service - `lexik_jwt_authentication.jwt_encoder`. And it's an instance
of `JWTEncoder`. Back in the authenticator, use this as the type-hint. Or, since
it looks like there's an interface this probably implements, you can use it instead.
Give this one more argument: `EntityManager $em` so we can query for the user.

I'll use a shortcut - option+enter on a mac - to initialize these fields. This created
the two properties and set them for me. Nice!

Head back down to `getUser()`. Step 1: we need to deice the token. To do that,
`$data = $this–>jwtEncoder->decode()` and pass it `$credentials` - that's our token
string. 

That's it! `$data` is now an array of whatever information we originally put into
the token. Fundamentally, this works just like a normal `json_decode`, except that
the library is also checking to make sure that the contents of our token weren't
changed. It does this by using our *private* key. This guarantees that nobody has
changed the username to some *other* username because they're a jerk. Encryption
is amazing.

It also checks the token's expires: our tokens last 1 hour because that's what we
setup in `config.yml`.

So, `if ($data === false`, then we know that there's a problem with the token. If
there is, throw a `new CustomUserMessageAuthenticationException` with `Invalid token`.
We'll talk about what that does in a second.

But if everything is good, get the username with `$username = $data['username']`.
Then, query for and return the uesr with
`return $this–>em–>getRepository('AppBundle:User')–>findOneBy()` passing
that `username` set to `$username`.

## checkCredentials()

If the user isn't found, this will return `null` and authentication will fail. But
if a user *is* found, then Symfony finally calls `checkCredentials()`. Just return
`true`: there's no password or anything else we need to check at this point.

That's it for the important stuff!

## Skip Everything Else (for now)

Skip `onAuthenticationFailure()` for now: we'll talk about that in a minute. And
for `onAuthenticationSuccess()`, purposefully do nothing: we want the authenticated
request to continue to the controller so we can do our normal work.

For `supportsRememberMe` - this doesn't apply to us - so return `false`. And keep
`start()` blank for another minute. With just `getCredentials()` and `getUser()`
filled in, our authenticator is ready to go. Let's hook it up!
