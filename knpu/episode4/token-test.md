# The Fetch a Token Endpoint Test

Almost every API authentication system - whether you're using JWT, OAuth or something
different - works basically the same. *Somehow*, your API client gets an access token.
And once it does that, it attaches it to all future requests to prove who it is and
that it has access to perform some action.

## How does the Client get a Token?

So, there are *two* parts to the process: how the client *gets* a token and how a
client *uses* that token. And actually, the first part is a lot more interesting
because there are a *bunch* of strategies for how a client should obtain a token.
For example, you could create an endpoint where the client submits their username
and password in exchange for a token. Or, you can do something more complex: like
use the OAuth flow. This is a good idea when you have third-party clients - like an
iPhone app - that need to gain access to your server on behalf of some user. Or, you
could use both strategies - GitHub lets you do that.

But the end result is always the same: the client gets a token. We're going to build
the first idea: a simple endpoint where the client can submit a username and password
to get back a token. That's something that will work for most APIs.

## The new Token Resource

Everything we've built so far has been centered around the Programmer resource. Now,
we'll be sending back tokens: and you can think of a Token as our second API resource:
the client will be able to create new tokens, and potentially, we could allow them
to delete tokens.

As always, we'll start with the test. Create a new class called `TokenControllerTest`.
Make it extend the handy `ApiTestCase` that we've been working on.

Add `public function testPOSTCreateToken`. Ok, let's think about this. First,
we're going to need a user in the database before we start. To create one, add
`$this->createUser()` with `weaverryan` and the super-secure and realistic password
`I<3Pizza`. Next, make the POST request: `$response = $this->client->post()` to
`/api/tokens`. That URL could be anything, but the most important thing is that it's
consistent with the `/api/programmers` we already have.

The last thing we need to do is send the username and password. And really, you can
do this however you want. But, why not take advantage of the class HTTP Basic Authentication.
To send an HTTP Basic username and password with Guzzle, add an `auth` option and
set it to an array containing the username and password.

And hey, reminder time! On production, you *will* make your API work over https.
The last thing we want is plain-text password flying all over the interwebs.

Below, assert that we get back a 200 status code, or you could use 201 - since technically
a resource is being created. Now, what should the response *look* like? Well, it
should be a token resource... which is really just a string. Use the asserter to
assert that the JSON at least contains a `token` property - we don't know exactly
what its value will be.

Looks cool! Copy the method name and run *only* this test:

```bash
./vendor/bin/phpunit --filter testPOSTCreateToken
```

This should fail... and it does! A 404 not found. Time to bring this to life!
