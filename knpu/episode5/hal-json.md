# HAL JSON

Google for "How to remove a mustard stain from a white shirt". I mean, Google for
"HAL JSON" - sorry, I'm dealing with some things over here.

This is one of a few competing *hypermedia* formats. And remember, *hypermedia* is
one of our favorite buzzwords: it's a media type, or format, - like JSON - plus some
rules about how you should semantically organize things inside that format. In human
speak, HAL JSON says:

> Hi I'm HAL! If you want to embed links in your JSON, you should put them under
> an `_links` key and point to the URL with `href`. Have a great day!

If you think about it, that's kind of similar to HTML. In HTML, there's the XML-like
format, but then there are rules that say:

> Hi, I'm HTML! If you want a link, but it in an `<a>` tag under an `href` attribute.

The advantage of having standards is that - since the entire Internet follows them -
we can create a browser that understands the significance of the `<a>` tag, and
renders them clickable. In theory, if all API's followed a standard, we could create
clients more easily deal with the data.

## Updating Programmer to use the new Links

So let's also update the `Programmer` entity to use the new system. Copy the whole
`@Relation` from `Battle` and replace the `@Link` inside of `Programmer`. Change
the `rel` back to `self` and update the expression to `object.getNickname()`. Make
sure you've got all your parenthesis in place. Oh, and don't forget to bring over
the `use` statement from `Battle`.

In `ProgrammerControllerTest`, the `testGetProgrammer` method looks for `_links.self`.
Add `.href` to this to match the new format.

Let's try it out!

```bash
vendor/bin/phpunit --filter testGetProgrammer
```

Yes!

## Should I Use HAL JSON?

The real benefit of using an official format is it just helps your API clients
get a little bit more sense of how your API works. Now we can tell somebody,
"Hey, our API returns HAL JSON responses and they can go read this
documentation and at least get some information about what that means. In fact,
now that we are using this format, we can advertise this with the content type
header in the response. In fact, that's what this application\hal+json is up
here. That's a custom content type. You can see the format's basically like
application\json but this HAL+ basically says there's additional semantic
meaning in this JSON and if you want to know more about it, then Google its
format.

So go back to programmer controller tests. In the original tests, where we
create the programmer, we can assert now that application\hal+json is equal to
response, get header, content type. And then because guzzle's weird and that
returns right, hit the zero key on that. So we're basically testing that our
API is going to advertise that whenever it returns a resource, it returns it
with this header. And to get that to work, open up the base controller and
search for create API response, which is what we're calling from all of our
controllers, to create the response. And just change this key down here to be
application\hal+json. Good for us. We're advertising our new content site.

So copy the test name and rerun PHP unit to make sure that's working.
Congratulations, you're no longer and island. You're using an official format.
