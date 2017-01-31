# POST To Create

***TIP
In this course we're using Symfony 2, but starting in [episode 4][episode_4],
we use Symfony 3. If you'd like to see the finished code for this tutorial
in Symfony 3, [download the code from episode 4][first_chapter_of_episode_4]
and check out the start directory!
***

Well hey guys! I've wanted to write this series for *years*, and now that
it's here, I'm so *pumped*! That's because even though building an API can be
really tough, the system we're about to build feels simple, and really a bit
beautiful.

We have another REST series on the site where we build the API in Silex and
learn the short list of REST concepts like resources, representations,
what status codes to return, what headers to set, how to format your JSON
and a few other buzzwords like hypermedia, HATEOAS and of course, don't forget
about our favorite: idempotency.

But in this series, I'll assume you have a basic grasp of this stuff and
we'll get straight to work. If you're confused by a term, head back to
that series to fill in any gaps.

## The Project

Ok, I've got the "start" directory for the project downloaded, I've configured
`parameters.yml` and I've already run `composer install`. So let's launch
the built-in web server:

```bash
php app/console server:start
```

Hey, it's Code Battles! This is the same awesome project we built in Silex 
for the other REST series. It already has a slick web interface - so we're 
going to build the API. To make sure we can login, let's create the database 
and load the fixtures:

```bash
php app/console doctrine:database:create
php app/console doctrine:schema:create
php app/console doctrine:fixtures:load
```

Now login with a fixtures user: `weaverryan` and the very secure password
`foo`.

## The Code Battles Web Interface

To understand the API we're going to build, let me give you a quick 60-second tour.
And please keep your hands and arms inside the project at all times.

The first resource is a programmer, and we start by creating one. Give it
a name, a clever tag line, choose one of the avatars and compile! Next, a
programmer has energy, and you can change that by powering them up. Sometimes
good things happen that give you power, sometimes bad things happen -- like a
case of the Mondays.

With some power, you can start a battle. These are projects, and projects
are the second resource. And when you select one, it creates our third resource:
a battle. Our programmer killed it! Each battle is between one programmer resource
and one project resource. On the homepage, you can see a list of all the battles our
programmer has bravely fought.

## POST to /api/programmers

So where do we start with the API? Well, other than logging in - which we'll
talk about later - the first thing we do on the web is create the programmer.
That's where we should start. Building an API is no different than building
for the web: you need to step back and *think* about your user-flow and build
things piece-by-piece in that order.

Open up `app/config/routing.yml`. I'm loading annotation routes from a `Controller/Web`
sub-directory. I put all my web stuff there because now I can create an `Api` directory
right next it and keep things organized.

In `routing.yml`, I'll keep two separate route imports: one for `Web/` and I'll
add a new one for `Api/`. Trust me - this will come in handy later:

[[[ code('9f34d08ba9') ]]]

Now create the new `ProgrammerController` - and make it extend Symfony's
`Controller` like normal:

[[[ code('c419a9544e') ]]]

Our first endpoint will be for *creating* Programmers, so let's start with
`public function newAction()`. Above it, setup the `@Route` annotation with
the URL `/api/programmers`. Let's also make it only respond to `POST` requests:

[[[ code('9bff631b62') ]]]

### URL Structures and HTTP Methods

Ok, we just made 2 interesting architectural decisions:

First, we're going to start all our API URI's with `/api`. That's opinionated,
and RESTfully speaking, it's wrong. REST says that if we want to return an
HTML *or* JSON representation of a programmer resource, we should have just
*one* URI - like `/programmers/HappyCoderCat`. This one URI should be able
to return both formats based on a header the client sends.

If you want to do this, awesome - go for it! But it's not easy to do, and
I'm not sure it's worth it. That's why we've separated the `Web` and `Api`
stuff into different controllers and URIs. Now we can focus *just* on getting
our API right.

The second architectural decision we made was to create a new resource 
by sending a POST request to that resource's collection URI - so `/api/programmers`. 
If you're curious why, watch our other screencast and learn about idempotency. 
And, in REST, you can make your URLs look however you want. But in practice, we're going to
use a very consistent pattern. Because even though you can make your URLs
super weird you probably shouldn't.

### "Testing" the POST Endpoint

We'll return a new `Response` from the controller: Let's do this!

[[[ code('ab233ae24b') ]]]

Ok, so the easy days of just refreshing our browser to try this out are gone:
we can't POST here directly in a browser. Now, a lot of people use Postman
or something like it to test their API. And while it's great, I think there's
a better way.

For now, create a new file - `testing.php` - right at the root of the project.
Inside, require Composer's autoloader:

[[[ code('2f869af994') ]]]

We're going to use the [Guzzle](http://guzzle.readthedocs.org) library to
hit our new endpoint and make sure it's working. I already installed it into
the project - so go directly to `$client = new Client([])` and pass it some
configuration:

[[[ code('8eb7e0227d') ]]]

***TIP
To install this same version of Guzzle into your project, use Composer to fetch
version 5.*:

```bash
composer require guzzlehttp/guzzle:~5.0
```
***

The first is `base_url` set to `localhost:8000`. Next, pass it a `defaults`
key - these are options that'll be passed, by default, to each request.
Set one option - `exceptions` - to `false`. Normally, if our server returns
a 400 or 500 status code, Guzzle blows up with an Exception. This makes it
act normal - it'll return a Response *always*. Trust me, that's nice!

Now make the request - `$response = $client->post('/api/programmers')`. Echo
the `$response` - it's an object, but has a really pretty `__toString` method
on it:

[[[ code('e65cc196c7') ]]]

Try it by hitting this file from the command line:

```bash
php testing.php
```

Ok, let's fill in the guts and make this work!


[episode_4]: https://knpuniversity.com/screencast/symfony-rest4
[first_chapter_of_episode_4]: https://knpuniversity.com/screencast/symfony-rest4/deny-access
