# The Battle Resource

WOW. Is it part 5 of the Symfony REST series already? Ok then, let's do this!

And this is a special course for me. When I started into all this REST API stuff,
there were tons of neat-sounding ideas and philosophies like hypermedia & HATEOAS.
Wowzer! Defining these terms is pretty easy... but putting them into practice? It
was a Ryan disaster.

So let's take those ideas on directly in this tutorial, and clear up what's hype,
and what is actually an evil ploy to prevent your awesome API from being released.

## Code Setup

And hey, I just had an idea! You should code along with me! We've gone to all this
trouble already to add a download for the code, so you might as well get it. Plus,
there are some kitten gifs in the archive.. ok, not really. Once you've unzipped
the code, move into the `start/` directory, you'll have the exact code I already
have here. Open the README for the setup instructions.

And if you've been coding along with previous courses, you're my favorite. But...
download the new code: I made a few small changes to the project.

Once you're ready, start the built-in web server with:

```bash
bin/console server:run
```

## Resources: Programmer + Project = (epic) Battle

Go to the frontend at `http://localhost:8000`  and login with `weaverryan`, password
`foo`. The API for programmer resource is done: you can create, edit, view and do
other cool programmery things.

But the *real* point of the site is to create epic *battles*, and here's how: choose
a programmer, click "Start Battle", choose a project and then watch the magic. Boom!

There are *three* resources in play: the programmer, the project and a battle, whose
entire existence is based on being related to these other two resources. And it turns
out, relating things in an API can get tricky.

The resources in your API don't always match up with tables in your database, but
they often do. Our `Entity` directory holds a `Programmer` entity *and* a `Project`
entity, which just has a `name` and `difficultyLevel`. Now check out `Battle`: it
has a `ManyToOne` relationship to `Programmer` and `Project`, plus a few other fields
like `didProgrammerWin`, `foughtAt` and `notes`.

Here's the goal: add an endpoint that will create new battle resources. How should
this endpoint look and act? You know the drill: let's design it first with a test.
