# Subordinate URL Structure

When an API client fetches information about a programmer, they might also want
a quick way to get details about all of the battles the programmer has fought. Ok,
we could add a link on programmer to an endpoint that returns all of that programmer's
battles.

This collection of battles is called a subordinate resource because we're looking
at the battles that belong to a programmer. It *feels* like a parent-child relationship.
Truthfully, this whole idea of "subordinate" resources isn't that important - and
it's usually subjective. But, if you're creating an endpoint and you realize that
it *feels* like a subordinate resource, a few things usually change.

To start: how should we setup the URL? Is it `/api/battles?username=` or `/api/battles/{nickname}`?
If you read up on REST API stuff, they'll tell you the URL structure never matters.
Ok, let's use `/hamburger`! No, that's stupid... unless your app is about delicious
hamburgers. For the rest of us, there *are* some sensible rules we should follow.

## Adding the Link

First, in `Programmer`, let's add a new link from the programmer to the battles
for that programmer, and then we'll create that endpoint. For the `rel`, let's
use `battles`. That could be anything: just be consistent. Whenever you link to a
collection of battles, use `battles`.

Everything else looks good. The route will *probably* need the nickname of the
programmer... we're not sure yet - because this endpoint doesn't exist. Let's create
it.

## The URL Structure

But wait Which controller should it go into: `ProgrammerController` or `BattleController`?
There's no right answer to this, but because these are battles for a specific programmer,
the battle is subordinate to the programmer. In these situations, I tend to put the
code in the parent resource's controller: `ProgrammerController`.

And actually, the biggest reason I do this is because of how we're going to structure
the URL. Make a `public fucntion battlesListAction()`. Above that, add `@Route()`
and the URL, which of course, *could* be anything. Make it `/api/programmers/{nickname}/battles`.

Check this out: the first three parts of the URL identify a specific programmer
resource. Then, `/battles` looks almost like a `battles` *property* on programmers.
That *feels* right... and that's all that matters.

For the name, use `api_programmers_battles_list` and copy that. Every part of this
is consistent and almost self-documenting.

Head back to `Programmer` and paste the route name. The big lesson about subordinate
resources is that it's ok to have them and that this is the best URL structure to
use. But if some other organization feels better to you, do it. This is one of those
REST topics you should *not* lose time thinking about.
