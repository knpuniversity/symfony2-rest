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
to be this programmer object here and I'll call `getNickname`. 
