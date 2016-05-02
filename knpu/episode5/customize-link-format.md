# Customize how your Links Render

As cool as all this HAL JSON stuff is, you need to build your API for whoever is
using it - maybe a JavaScript frontend, a mobile app or your customers themselves.
And honestly, I don't think that the standardized formats - like Hal - are all that
understandable or useful. This `_embedded` thing? To me it's just ugly.

I also don't love hiding the URL under an object with an `href` key. So let's suppose
that we're building a JavaScript frontend, and it'll work better if the link URL's
appeared *directly* under the `_links` key - without the `href`.

Let's do that! The HATEOAS library we installed really just helps you add relations
to a class: both link relations and embedded relations. And fortunately, the library
let's you control exactly how these are added to your response.

## Custom Serializer

In `AppBundle`, in the `Serializer` directory, create a new class called
`CustomHATEOASJsonSerializer`. Make it extend a class called `JsonHalSerializer`:
this is the current class responsible for adding links in the HAL format.
In fact: open up the class.

It has two method. `serializeLinks` is responsible for reading the `Relation` annotations
and adding them to the JSON with `_links`. `serializeEmbeddeds` adds any embedded
relations under the `_embedded` key.

For now, let's focus on changing how the links render only. Go to the "Code -> Generate"
menu - command+N on a Mac - and hit "Override Methods". Override `serializeLinks`.

Re-open the parent method and then the interface: I want to copy all that good PHPDoc
so we get auto-complete. Paste it above our method and auto-complete the `Link`
to get its `use` statement.

Alright: this should be easy.

Create a `$serializedLinks` array and `foreach` over the `$links` variable. Each
of these is a `Link` object, and contains the configuration for one annotation.
Now, just create the format we want: `$serializedLinks[]`, with `$link->getRel()`.
Instead of setting this to an array with an `href` key, simply set it to `$link->getHref()`.

Perfect! Finally, at the bottom, we need to add the `_links` property. Do that with:
`$visitor->addData('_links', $serializedLinks)`.

With any luck, that should give us a simpler format without that `href`.

## Registering the Serializer

To hook this up. You guys can probably guys step 1: in `app/config/services.yml`,
register this as a service. How about: `custom_hateoas_json_serializer`. Set its
class to that same thing. And we don't have any constructor args yet.

Finally, copy the service name. To tell the bundle to use *our* class instead of
the existing one, open up `config.yml`. Now, without even looking at its docs, we
can get a list of the configuration for this bundle by going to the terminal and
running:

```bash
./bin/console debug:config
```

Thanks to the bundle, there's a new valid config key called `bazinga_hateoas`. Pass
that to the same command:

```bash
./bin/console debug:config bazinga_hateoas
```

Ah, that `serializer.json` key looks like our target.

Back in `config.yml`, add `bazinga_hateoas`, `serializer`, `json` and then paste
our service name. That should do it!

## Changing our Tests Back

But don't run the tests *quite* yet: we *know* some things will be broken. In
`BattleControllerTest`, take off the `href` we just added: it should be `_links.programmer`.
And in `ProgrammerControllerTest`, under `testGetProgrammer`, do the same.

Phew! That's a lot of changes, so let's re-run the *entire* test suite:

```bash
./vendor/bin/phpunit
```

Hey, it passes! I must've left a `debugResponse()` in there somewhere: but that's
nothing to worry about - we're green!
