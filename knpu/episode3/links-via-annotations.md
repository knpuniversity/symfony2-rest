# Links via Annotations

This next part is one of my favorite things ever to record. It's going to be so cool!

In our `ProgrammerControllerTest` we call this key `$uri` because, well...why not?
But if you remember from our pagination, we actually included links in here and we
prefixed them with `_links` because we thought "Hey, under there might be a good place to put
our links!" Well...isn't this URI up here just a link? 

When we worked on the pagination we also made one of the links be called `self` which is a
link to the current page. This is the exact same thing. 

What I'm arguing is, for consistency we should actually call this `_links.self`. Now, this highlights
one other thing. With a decent bit of work we just added a link to our programmer. But we'll
probably want to do this to a bunch of other entities as well. We may even want to start generating
other links as well beyond `self` once we have more relations. For example, a url from a programmer
to see a collection of battles that programmer has been in. 

What I also want to do is create a new annotation that allows me to do the following above any class
that's going to be serialized. `@Link("self")`, then `route = "api_programmers_show"` and next
`params = { }` and whatever parameters need to be filled in. In our case it's `"nickname":` and then
over here what I fill in for nickname I'm going to use an expression from Symfony's expression engine.
I'll assume that we're going to pass a variable called `object` to the expression engine which is going
to be this programmer object here and I'll call `.getNickname()`. Obviously, this doesn't work yet, 
but it will in a few minutes!

Every annotation has a class behind it, so we'll need to create one for this link annotation. In `AppBundle`
create a new directory called `Annotation`. Inside there create a new PHP class called `Link` in the `AppBundle\Annotation`
namespace.

To make this annotation we have to give it a couple of annotations. The first one being...well `Annotation`.
And the second one is `@Target` which will be `"CLASS"` meaning that it lives on classes.

Inside of here, for every option that we need on our annotation, like `route` and `params` and `self` which will
be a name option, we'll create public properties. `public $name;`, `public $route;` and `public $params = array();`
The first property becomes the default property which is why we don't have to have `name = self` in the first part.

Name and route are going to be required, so plug in an `@required` above there as well, which is pretty cool!
And that's all we need to create an annotation. Inside of `programmer` we see that every annotation needs a
use statement. We already have our `Serializer`, `Assert` and `ORM` use statements. Create a use statment directly
to the class itself for `Link`. This hooks the annotation up with the class we just created. 

Ok... so how do we read annotations? Great question, and good news it's simple! Doctrine has a library that does this
and there's already a service inside of Symfony's container that does this. 

Inside `LinkSerializationSubscriber` we'll inject that as a second argument. It is a `Reader` interface from
`Doctrine\Common\Annotations` and we'll call it `$annotationsReader`. I'll hit option+enter, initialize fields, 
to get that setup on property. 

Oh, and before I forget, in `services.yml` we'll inject that in with `@annotation_reader`, how easy is that!?

Too easy, back to work! Delete all of this in `onPostSerialize` and start with `$object = event->getObject`.
Now it's time to read the annotations off of that object. To do that type `@annotations = $this->annotationReader`
`->getClassAnnotations()` and we need to pass that a new reflection object so plug in `new \ReflectionObject()`
and to that we'll pass our `$object`. That's it!

There could be lots of annotations on here but we only want the link annotation. We'll add an if statement for
that in a second. First, create `$links = array ()` where we'll start to put together all the links that we need.
And now `foreach ($annotations as $annotations)` and the first thing that goes in there is `if ($annotation instanceof Link)`
then those are the ones we're actually interested in. The data on here is a public property so to get the URI 
we can just say `$this->router->generate()` and give it `$annotation->route` and `$annotation->params`. 
How sweet is that? Now our params are contained in this expression thing which we aren't dealing with yet but 
we will get to it in a second.

Here we can say `$links[$annotation->name]` to get the name or the ref of that link, `= $uri;`. Down at the bottom,
finally we can say `$visitor->addData()` just like before and add `_links` and set it to our `$links;`. That other
than the expression stuff should take care of it. 

Instead of heading to the test, let's head directly to api/programmers in our browser. Look at that! The embedded
programmer entities actually have a link called `self`. It worked!

Of course, we have a problem here with `object.getNickname` and open paranthesis, close parenthesis because our
expression isn't being done yet. But, fundamentally it's working!

Before we fix that expression we can take off the class portion down here because we want to operate on all classes.
And when we do that, things still work. But, if I clear my cache in the terminal, `./app/console cache:clear` which
is not something you normally need to do but we're into some more advanced stuff right now. When we refresh on the 
browser we get a giant error that says that there is already data for _links which should seem a bit weird. But,
remember one of the things that we're serializing is the paginated collection itself which already has an `_links`
property. 

For better or worse, the JMS Serializer library doesn't let you run over that and replace that data. To fix this
I'll say if we have some links then let's add some. A better fix, which I'll leave to you, would be to go into
`PaginatedCollection` and replace these links here with the same `@link` annotation. This would be a little bit
of work but I believe in you!

Refresh our browser again. Things look good! Let's go on to fixing that expression stuff. To use the expression
engine we're going to need an expression engine object. We could create one and register it as a service, this would
be a particularly great idea if we had some custom functions we wanted to add into it. Instead, I'm instantiating a new expression engine right inside of the constructor and setting it to a property, `$this->expressionEngine = new ExpressionLanguage` from the expression language component. 

Let's go ahead and rename this to what it should be, `expressionLanguage`. Later, if I want to register this as a
service and configure it to have some caching I could add that as another argument. But this is good enough for
now. 

Down here for `params` we need to run that through the expression engine. I'll wrap this in a call to `$this->resolveParams()`
and I'll pass it the params and also the object because we need to pass that into the expression itself. 

Make a new `private function resolveParams()`, get that array of params in there and let's start looping over that.
`$params as $key => $param`. For each param we'll just replace it with `$this->expressionLanguage ->evaluate()` and
we'll give it the param which is the expression itself, that's what we have right here. Now we just need to pass it 
a variable which is the object that's being serialized and this should also be added as a second argument up here.
To wrap this up `return $params;`. This will run things through Symfony's expression language which is a lot like Twig
and really handy. 

Back to the browser and refresh. There it is! How about our test? Hey, that's passing too! Nice! 

In just a few short minutes we made an entirely resuable linking system. I will admit that this idea was stolen
from a library called Hateoas which is something will cover in an upcoming episode in this series. Stay tuned
for that because it's really really cool!



