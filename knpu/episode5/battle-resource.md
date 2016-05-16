# The Battle Resource

Hey guys! Finally part 5 - and it's *special*! It's my chance to take on some buzzwords
directly: like hypermedia & HATEOAS.

Now, defining these terms is easy... but putting them into practice? For me, it was a
disaster.

Let's take those ideas on directly in this tutorial, and clear up what's hype,
and what is actually an evil ploy to prevent your awesome API from being released.

## Code Setup

And hey, I just had an idea! You should code along with me! We've gone to all this
trouble already to add a download for the code, so you might as well get it. Plus,
there are some kitten gifs in the archive.. ok, not really. Once you've unzipped
the code, move into the `start/` directory, you'll have the exact code I already
have here.

There's also a README file that has a few other setup instructions you'll need.

And if you've been coding along with previous courses, you're my favorite. But...
download the new code: I made a few small changes to the project.

Once you're ready, start the built-in web server with:

```bash
bin/console server:run
```

## Resources: Programmer + Project = (epic) Battle

Go to the frontend at `http://localhost:8000` and login with `weaverryan`, password
`foo`. The API for the programmer resource is done: you can create, edit, view and do
other cool programmery things.

But the *real* point of the site is to create epic *battles*, and here's how: choose
a programmer, click "Start Battle", choose a project and then watch the magic. Boom!

There are *three* resources in play: the programmer, the project and a battle, whose
entire existence is based on being related to these other two resources. And it turns
out, relating things in an API can get tricky.

The resources in your API don't always need to match up with tables in your database, but
they often do. Our `Entity` directory holds a `Programmer` entity *and* a `Project`
entity, which just has `name` and `difficultyLevel` fields. Now check out `Battle`: it
has a `ManyToOne` relationship to `Programmer` and `Project`, plus a few other fields
like `didProgrammerWin`, `foughtAt` and `notes`.

Here's the goal: add an endpoint that will create a new battle resource. How should
this endpoint look and act? You know the drill: let's design it first with a test.
