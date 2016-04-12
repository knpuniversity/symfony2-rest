# Weird Tagline Endpoint

Most of our endpoints have been pretty straightforward. We’re creating a program, we’re updating a program, or creating a battle, but in the real world, sometimes you just get weird endpoints, and for me, this was one of the most confusing parts, when you have so many endpoints that is not really editing something, but almost maybe performing an action.

There are all kinds of different weird scenarios that you run into, and I want to talk about those directly, so here’s the first challenge. Suppose that you decide that it would be really nice to have an endpoint where your client can edit the tagline of a programmer directly.

Now, technically, in our API, that’s already possible. You can send a patch request to the programmer endpoint and only send the tagline, and you’ll be fine. Again, we’re building our API for our clients, and it might be more convenient if you can actually do something a little bit different. Let me show you what I mean.

In ProgrammerControllerTest, let’s design this new endpoint first. Make a public function, sorry, test Edit Tagline. Now scroll up to the top and grab our $this->createProgrammer line that we’ve been using, and let’s give this a specific tag line of the original UnitTester. Okay, so if we were gonna have an endpoint where literally the only thing you can do is edit the tag line, how would that look?

Well, you’re not really editing the programmer anymore because if you have an endpoint where you’re able to edit the programmer, you should be able to edit any of the fields on the programmer. One way to think about this is that the tagline is a subordinate resource, or also kind of a property, on the programmer.

Remember, every URI is for a resource, so what would be URI look like directly to the tagline resource for a programmer? Well, to would probably look like /api/programmers/unittester/tagline. In fact, if you think of that as its own resource, then all of a sudden, you could decide to create a git endpoint to just retrieve just the tagline, or a put endpoint to update just the tagline because that is its own little resource.

This isn’t always done, but this is one of the weird things that you can start doing with your API. That means that we’re gonna to actually make a request to this $this->client->put () because we’re gonna to be doing an update; we’re going to be sending all of the data we need this endpoint, /api/programmers/ UnitTester/tagline.

It will pass the normal authorization header, and then we also need to pass the new tagline. Normally, what we do is we send a json-encoded body of all of the fields, but technically now, we’re just editing the tag line resource. The tagline resource is nothing but text, so there’s nothing wrong with sending a json-encoded array here with the tagline in it, but to be most semantically correct with REST, you could just send it as plain text. We are sending a plain text message to modify this plain text tagline resource.

Alright, finish this off with $this->assertEquals 200 for the status code, and then let’s assert what we get back. Whenever we edit or create something, we always get back at the resource that we just edited or created. Again, this tagline resource is just a string. Instead of expecting json back, which you could do, we’re just going to look for that literal text, so $this->assertEquals that new tagline is what we get back, or the $response->getBody, so literally, it’s going to send us text back.

Now, you don’t have to do it that way. We could say, “Look, you’re really editing the unit tester programmer resource, so you could decide to actually send back the entire programmer resource, and that would be totally fine,” but this is just a really interesting way to think about, but ultimately, whatever you do, don’t think about it too much,  just do whatever is gonna be easiest for your API client.

Alright, let’s hook this up. At the bottom of ProgrammerController, let’s add a public function testEditTagline. We already know that the route is going to be /api/programmers/{nickname}/tagline. Also, let’s add a @method because we know this is gonna be a put request.

Like before, let’s go straight and type in the programmer so that Doctrine will query for that programmer based on the nickname automatically, and obviously, we’re gonna need the request object as well.

I could use a form on this like I’ve been doing before, but this is just so simple, let’s just get it done quickly. $programmer->setTagLine ($request->getContent ()), and that’s it. Literally read that text from the request content and set that on the programmer. Now, we’re just gonna do this. $em = $this->getDoctrine ()->getManager(), $em->persist ($programmer), and $em->flush(). That’s it.

Now, the return statement is not gonna be any json structure, we’re just gonna return a plain Jane new response object with programmer->getTagLine, that new tag line, a 200 status code, and a content type header because if we don’t set the content type header, Symphony’s gonna default to text/html, but in this case, we actually know this is just a plain text message, so if we’re really responsible, we would set this it text/plain. That is a good-looking, weird endpoint. Copy the test method name. Let’s run ./vendor/bin/phpunit --filter, paste that. Very nice. Now, let me show you a weirder endpoint.
