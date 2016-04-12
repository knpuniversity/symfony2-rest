# Subordinate URL Structure

With three resources, we have programmers, battles, and projects, and they’re all kind of on the same level. Think of them as sort of top level, very important resources, and they link together. Sometimes, you have resources that are almost more like children of other shores resources, and we call these subordinate resources.

Let me give you an example. Suppose it would be convenient for our API to return a link on a programmer resources to all of the battles for just that programmer. Now, so far, it would be very easy for us to create an endpoint that returns all of the battles in the system. That would be something like /api/battles, but now I just want the battles for a specific programmer.

How do we set that up, and how does the URL structure look? Remember, if you read about all this REST API stuff, they’re gonna tell you the URL structure doesn’t matter. In other words, if I want to make an endpoint that returns all of the battles for a specific programmer, I could make it look like /foo/bar/ the programmer’s nickname, /hamburger, but that’s ridiculous. Let me though you the right way to handle subordinate resources.

First, in Programmer, let’s add a new link from the programmer to the battles for the programmer, and then we’ll hook up that endpoint. For the rel, let’s use battles, and that could be anything, just make sure that’s consistent. Whenever you link to a collection of battles, use battles.

Everything else looks good. Our route will probably need the nickname of the programmer. The only question is what route name do we use here because we don’t have an endpoint yet that returns the battles for a specific programmer.

Leave this for now. The next question is, what controller should this go into? Should it go into battle controller, or should it go into programmer controller? Because it’s kind of a mixture of those. There’s no right answer to this, but because we’re thinking of these as battles for a specific programmer, the battle in this case is a subordinate to the programmer, and I tend to see this most commonly live inside of the programmer controller.

The biggest reason why I say this is because of how we’re gonna structure the URL. Let’s make a public function, battlesListAction. This will be listing the battles from within a programmer. Above that, add the route, @route, and the URL, which, of course, could be anything, we’re gonna make it /api/programmers to be consistent with every other endpoint in this controller, / the nickname of the controller, and then /battles.

This is the way you wanna set up the URL because you can see, the first three parts basically identify a specific program resources, and then /battles almost looks like it’s a property on programmer, which is kinda cool.

Give this a name. Let’s use api_programmers, which is consistent with the rest of this controller, _battles_ list, and copy that. Awesome. If you structure your URLs in this way, you’re gonna keep things very consistent, very happy, that basically documents itself. This is very obviously gonna return battles for this specific programmer.

Then go back to programmer, and let’s stick in that route name. Lesson No. 1 with subordinate sources is it’s okay to have them, and this is probably the best URL structure to use. But if you didn’t wanna do this, you could do something else. Don’t stress out about it too much. Alright, let’s hook up this new collection resource.
