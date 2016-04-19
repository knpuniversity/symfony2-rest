# The "Entry Point" & Multiple Firewalls

The authentication system works great! Except for how it behaves when things go
wrong. When an API client tries to access a protected endpoint but forgets to send
an `Authorization` header, they're redirected to the login page. But, why?

Here's what's going on. Whenever an anonymous user comes into a Symfony app and
tries to access a protected page, Symfony triggers something called an "entry point".
Basically, Symfony wants to be super hip and helpful by *instructing* the user that
they need to login. In a traditional HTML form app, that means redirecting the user
to the login page.

But in an `api`, we instruct the API client that credentials are needed by returning
a 401 response. So, how can we control this entry point? In Guard authentication,
you control it with the `start()` method.

## The start() Method

Return a new `JsonResponse` and we'll just say `error => 'auth required'` as a start.
Then, set the status code to 401.

To see if it's working, copy the `testRequiresAuthentication` method name and run
that test:

```
./vendor/bin/phpunit --filter testRequiresAuthentication
```

Huh, it didn't change *anything*: we're still redirected to the login page. I thought
Symfony was supposed to call our `start()` method in this situation? So what gives?

## One Entry Point per Firewall

Open up `security.yml`. Here's the problem: we have a single firewall. When an anonymous
request accesses the site and hits a page that requires a valid user, Symfony has
to figure out what *one* thing to do. If this were a traditional app, we should redirect
the user to `/login`. If this were an API, we should return a 401 response. But our
app is *both*: we have an HTML frontend and API endpoints. Symfony doesn't really
know what *one* thing to do. The `form_login` authentication mechanism has a built-in
entry point and *it* is taking priority. Our cute `start()` entry point function
is being totally ignored.

But no worries, you can control this! You could add an `entry_point` key under your
firewall and point to the authenticator service to say "No no no: I want to use my
authenticator as the *one* entry point". But then, our HTML app would break: we *still*
want users on the frontend to be redirected.

Normally, I'm a big advocate of having a single fireall. But this is a *perfect*
use-case for splitting into two firewalls: we really do have two very different
authentication systems at work.

## Adding the Second Firewall

Above, the main firewall, add a new key called `api`: the name is not important.
And set `pattern: ^/api/`. That's a regular expression, so it'll match anything
starting with `/api/`. Oh, and when Symfony boots, it only matches and uses *one*
firewall. Going to `/api/something` will use the `api` firewall. Everything else
will match the `main` firewall. And this is *exactly* what we want.

Add the `anonymous` key: we may still want some endpoints to not require authentication.
I'll also add `stateless: true`. This is kind of cool: it tells Symfony to *not*
store the user in the session. That's perfect: we expect the client to send a valid
`Authorization` header on *every* request.

Move the guard authenticator up into the `api` firewall. And that should do it! Now,
it will use the `start()` method from *our* authenticator.

Give it a try!

```
./vendor/bin/phpunit –filter testRequiresAuthentication
```

It passes! Don't rush into having multiple firewalls, but if you have two very different
ways of authentication, it *could* be useful.
