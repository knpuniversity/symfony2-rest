# Adding Links via Annotations

Oh man, this chapter will be one of my *favorite* ever to record, because we're
going to do some sweet stuff with annotations.

In `ProgrammerControllerTest`, we called this key `uri` because, well...why not?
But when we added pagination, we included *its* links inside a property called `_links`.
That kept links separate from data. I think we should do the same thing with `uri`:
change it to `_links.self`. The key `self` is a name used when linking to, your, "self".

Renaming this is easy, but we have a bigger problem. Adding links is too much work.
Most importantly, the subscriber only works for `Programmer` objects - so we'll need
more event listeners in the future for other classes.

I have a different idea. Imagine we could link via annotations, like this: add `@Link`
with `"self"` inside, `route = "api_programmers_show"` `params = { }`. This route
has a `nickname` wildcard, so add `"nickname":` and then use the expression `object.getNickname()`.
That last part is an *expression*, from Symfony's expression language. You and I
are going to build the system that makes this work, so I'm going to assume that we'll
pass a variable called `object` to the expression language that is this `Programmer`
object being serialized. Then, we just call `.getNickname()`.

Of course, this won't work yet - in fact it'll totally bomb if you try it. But it
will in a few minutes!

## Creating an Annotation

To create this cool system, we need to understand a bit about annotations. Every
annotation - like `Table` or `Entity` from Doctrine - has a class behind it. That
means *we* need a `Link` class. Create a new directory called `Annotation`. Inside
add a new `Link` class in the `AppBundle\Annotation` namespace.

To hook this annotation into the annotations system, we need a few annotations: the
first being, um, well, `@Annotation`. Yep, I'm being serious. The second is `@Target`,
which will be `"CLASS"`. This means that this annotation is expected to live above
class declarations.

Inside the `Link` class, we need to add a public property for *each* option that
can be passed to the annotation, like `route` and `params`. Add `public $name;`,
`public $route;` and `public $params = array();` The first property becomes the default
property, which is why we don't need to have `name = "self"` when using it.

The `name` and `route` options are required, so add an extra `@Required` above them.
And... that's it!

Inside of `Programmer`, every annotation - except for the special `@Annotation`
and `@Target` guys, they're core to that system - needs a use statement - we already
havesome for `Serializer`, `Assert` and `ORM`. Add a `use` statment directly to the
class itself for `Link`. This hooks the annotation up with the class we just created. 

## Reading the Annotation

Ok... so how do we read annotations? Great question, I have no idea. Ah, it's easy,
thanks to the Doctrine annotations library that comes standard with Symfony. In fact,
we already have a service available called `@annotation_reader`.

Inside `LinkSerializationSubscriber`, inject that as the second argument. It's an
instance of the `Reader` interface from `Doctrine\Common\Annotations`. Call it
`$annotationsReader`. I'll hit option+enter and select initialize fields to get that
set on property. 

And before I forget, in `services.yml`, inject that by adding `@annotation_reader`
as the second argument. So easy.

Too easy, back to work! Delete all of this junk in `onPostSerialize` and start with
`$object = $event->getObject()`. To read the annotations off of that object, add
`$annotations = $this->annotationReader->getClassAnnotations()`. Pass that that a
`new \ReflectionObject` for `$object`. That's it!

Now, the class *could* have a lot of annotations above it, but we're only interested
in the `Link` annotation. We'll add an if statement to look for that in a second.
But first, create `$links = array()`: that'll be our holder for any links we find.

Now, `foreach ($annotations as $annotations)`. Immediately, see if this is something
we care about with `if ($annotation instanceof Link)`. At this point, the annotation
options are populated on the public properties of the `Link` object. To get the URI,
we can just say `$this->router->generate()` and pass it `$annotation->route` and
`$annotation->params`.

How sweet is that? Well, we're not done yet: the params contain an expression string...
which we're not parsing yet. We'll get back to that in a second.

Finish this off with `$links[$annotation->name] = $uri;`. At the bottom, finish with
the familiar `$visitor->addData()` with `_links` set to `$links;`. Other than
evaluating the expression, that's all the code you need.

Check this out by going to `/api/programmers` in the browser. Look at that! The embedded
programmer entities actually have a link called `self`. It worked!

Of course, the link is totally wrong because we're not evaluating the expression
yet. But, we're really close.
