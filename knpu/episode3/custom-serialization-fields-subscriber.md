# Custom Serialization Fields Subscriber

One of the things that makes doing API's in Symfony so powerful is the seralizer
that we've been using. We can just give it objects, whether they are entities or
something else, and it just seralizes all of their properties. Of course we have
control over which ones get serialized with the exclusion policy and different
annotations to rename what a property is called when we expose it through the API.

We can even add virtual properties, we could make a function inside of our class
and by adding `virtualProperty` on it we can add another field to our JSON response 
that's not actually a property on the class. That's great, but it still leaves the last
1% which are cases when you can't use the virtual property because what you need to
return requires access to some other service.

A perfect example of this is if we want to include the URL to the programmer in the 
representation of it. We can't just create a public function inside of this programmer
class and tag it as a virtual property because we need the router service in order to 
generate the URL. This can be a really frustrating thing, where you say "Nevermind, I'm
not using the serializer anymore!" and stomp off to your bedroom to play video games.
But this is really easy to overcome with an event subscriber on the serializer.

