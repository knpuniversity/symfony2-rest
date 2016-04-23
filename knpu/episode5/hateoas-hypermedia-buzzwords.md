# HATEOAS, Hypermedia & Buzzwords

These days, you expect a tutorial on REST to immediately throw out some buzzwords:
like hypermedia and HATEOAS. But I've tried *not* to mention these because honestly,
in practice: they're more trouble than good.

But, there are some cool parts! It's time to learn what these mean, what parts are
good, and what parts almost drove me to drinking.

## Hypermedia: Really Caffeinated DVD's

First, hypermedia! This does *not* involve slamming red bulls and binge-watching
Netflix. Here's the story: JSON is known as a media type. If I tell you that I'm
giving you a JSON string, you understand how to parse it. XML is another media type:
if I give you XML, you can understand its format.

The difference between media and *hypermedia* is that hypermedia is a format that
includes links. For example, if you used a JSON structure that consistently
put links under a `_links` key, well, you could claim that you just created your
own *hypermedia* format: a JSON structure where I know - semantically - what `_links`
means: it's not day: it links to *other* resources.

The most famous hypermedia format is ... drumroll HTML! It's basically an XML media
type that has built-in tags for links: the `a` tag. Actually, `<form>`, `<img>`
`<link>` and `<script>` are also considered "links" to other resources.

So calling something a hypermedia format is just a way of saying:

> Hey, when we return the JSON data, what if we added some rules
> that say any links we want to include always live in the same place?

And even though I avoided the word - *hypermedia* - in a sense, we're already returning
a hypermedia format because we always include links under an `_links` key. We'll
talk more about this a little later: there are actually "official" hypermedia formats
you can adopt for your API.

## HATEOAS

Ready for buzzword #2? HATEOAS... or HAT-E-OAS... or H-ATE-OAS... nobody really knows
how to say it. Anyways, the throat-clearing acronym stands for:

> hypermedia as the engine of application state

Whoa. Let's unpack that monster. It's actually a cool idea.

Application state refers to *which* endpoint a client is currently using. As the
client does more things - like creating a programmer and then starting a battle -
they're moving through different "application" states, the same way that someone
moves through the pages on your web site.

Normally, the client figures out *which* endpoint - or state - they need next by
reading some API documentation. But HATEOAS says:

> What if we didn't write API documentation and instead, every response
> we send back self-documents what endpoints you might want next via links?

So when we send back a programmer resource, it would contain a link for every
other possible thing the client might want to do next - like starting a battle
with that programmer.

In an ideal world, you would stop writing documentation and just say:

> Hey, use my API! Every time you make a request, I'll include details about
> what do to next.

In reality, HATEOAS is a fantasy, at least for now. For it to work, links
would need to be able to contain what HTTP method to use, what fields to send, and
what those fields mean. It's hard, way too hard for most people right now, including
me.

Here's the right way to navigate this mine field. Instead of saying:

> I'm going to include so many links that I don't have to document anything!

You should say:

> I'm going to add links that might be helpful, but also write documentation.

So let's do that: and take this idea in our app up to a new level.
