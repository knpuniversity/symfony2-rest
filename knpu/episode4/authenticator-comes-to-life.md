# Bring the JWT Authenticator to Life

The authenticator class is done - well done *enough* to see it working. Next, we
need to register it as a service. Open up `app/config/services.yml` to add it:
call it `jwt_token_authenticator`. Set its class to `AppBundle\Security\JWTTokenAuthenticator`. 

And instead of adding an `arguments` key: here's your permission to be lazy! Set `autowire`
to `true` to make Symfony guess the arguments for us.

Finally, copy the service name and head into `security.yml`. Under the firewall,
add a `guard` key, add `authenticators` below that and paste the service name.

As *soon* as you do that, Symfony will call `getCredentials()` on the authenticator
on *every* requst. If we send a request that has an `Authorization` header, it should
work its magic.

Let's try it! Run our original `testPOSTprogrammer` test: this *is* sending
a valid JSON web token.

```bash
vendor/bin/phpunit --filter testPOSTprogrammer
```

And this time... it passes!

Hold on, that's pretty amazing! The authenticator automatically decodes the token
and authenticates the user. By the time `ProgrammerController` is executed, our user
is logged in. In fact, there's one other spot we can *finally* fix.

Down on line 37, we originally had to make it look like *every* programmer was being
created by `weaverryan`. Without authentication, we didn't know *who* was actually
making the API requests, and since every Programmer needs an owner, this hack was
born.

Replace this with `$this->getUser()`. That's it.

Our controller doesn't know or care *how* we were authenticated: it just cares that
`$this->getUser()` returns the correct user object.

Run the test again.

```bash
vendor/bin/phpunit --filter testPOSTprogrammer
```

It still passes! Welcome to our beautiful JWT authentication system. Now, time to
lock down every endpoint: I don't want other users messing with my code battlers.
