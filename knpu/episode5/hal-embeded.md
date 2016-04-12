# Hal Embeded

Looking at the hal+json example here, there are actually three different parts of the json. You have the actual data themselves, like currentlyProcessing, shippedToday, and whatever other fields you have, you have _links, and you also have _embedded, which are actually like related objects that are embedded right in this response.

It turns out these are also known as relations. In other words, there are two ways, if you have a response, if you have your resource. There are two ways to add relation to it. You could add a link to another relation so that the client could make another request to get it, or you can choose to embed that entire related object inside.

The choice is totally up to you based on whatever’s most convenient to your client. This library handles this really nicely. Let’s pretend that when we’re returning our battle resource, we wanna include programmer as a link, but we also just wanna embed the programmer entirely.

To do that, after href, add an embedded=expr, and then object.getProgrammer. You can see what this looks like. Go into to battle controller test, and right at the bottom, let’s do a this->debugResponse. Perfect.

Copy that method name, run over at ./vendor/bin/phpunit --filter, paste that, and let’s check it out. Okay, perfect. We’ve still got the programmer link, but now we have an entire embedded programmer object. When you set up these Hateoas/Relations, you can choose whether you wanna include the href, the actual _links, or whether you wanna have an embedded object, or if you want to, you can just have both the them, which is what we have in this case.

Now that we know what it looks like, let’s go and add a test that looks specifically for this. How about $this->asserter()->assertResponsePropertyEquals with $response, and we know this is gonna be looking for embedded.programmer, and then one of the fields should be nicknamed, and that should be set to our friend Fred. We go back, run that, and now it passes. There are two different ways to relate things, a link, or just embed the entire object.
