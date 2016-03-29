# Start Securing the App!

You again? Get outta here.... punk... is what *we* will be saying soon to API clients
in this tutorial that don't have valid credentials! Yep, welcome back guys, this
time to a tutorial that's making security exciting again! Seriously, I'm *pumped*
to talk about authentication in an API... and in particular, a really powerful tool
called JSON web tokens.

To make sure your JSON web tokens *extra* are the envy of all your friends, code
along with me by downloading the code from any of the tutorial pages. Then, just
unzip it and move into the `start/` directory. I already have that `start` code
in my `symfony-rest` directory.

I also upgraded our project to Symfony 3! Woohoo! Almost everything we'll do will
work for Symfony 2 or 3, but there are a few differences in the directory structure.
We have a tutorial on upgrading to Symfony 3 if you want to learn the differences.

Let's start the built-in web server with:

```bash
bin/console server:run
```

And if you just downloaded the code, open the README and follow a few other directions.

## The (sad) State of our App's Security

Ok, our app is Code Battles! It has a cool web interface and you can login with
`weaverryan` and password `foo`: super secure! Here, we can create programmers and
start battles. And our API already supports *a lot* of this stuff, and a bit more.

Open up `ProgrammerController` inside the `Controller/Api` directory. Awesome! We
can already create, fetch and update programmers. AND, we've got a pretty sweet test
um, suite... that checks these endpoints.

Ready for the problem? Our API has *no* security! The horror! Anonymous users are
able to create programmers and then change the avatar on other programmers. It's
chaos!

On the web interface, you need to be logged in to do any of these things. Let's make
the API work the same way.

## Testing for Security

As always: we need to start by writing a test. In `ProgrammerControllerTest`, add
a new `public function testRequiresAuthentication`. Let's make an API request to
an endpoint thta *should* be secured and then assert some things. Start with
`$response = $this->client->post('/api/programmers')`. Send this a valid JSON body.

Ok, if our API client tries to anonymously access a secured endpoint, what should
be returned? Well, at the very least, assert that the response status code is 401,
meaning "Unauthorized".

Ok! Let's go make sure this fails! Copy the method name and find the terminal. Run:

```bash
./vendor/bin/phpunit --filter testRequiresAuthentication
```

It fails with a validation error: it *is* getting beyond the security layer and executing
our controller. Time to lock that controller down!

## Securing a Controller

Open `ProgrammerController`. How do we require the API client to be authenticated?
The *exact* same way you do in a web application. Add `$this->denyAccessUnlessGranted('ROLE_USER')`.

That's it. I'm using `ROLE_USER` because *all* of my users have this role - you could
also use `IS_AUTHENTICATED_FULLY`.

Ok, back to the test! Run it!

```bash
./vendor/bin/phpunit --filter testRequiresAuthentication
```

Oh, *interesting* - it's a *200* status code instead of 401. Look closely: it redirected
to the login page. So, it's *kind* of working... you can't add programmers anonymously
anymore. But clearly, we've got some work to do.
