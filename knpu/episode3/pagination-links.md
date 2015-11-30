# Pagination Links

The response *is* returning a paginated list, and it even has extra `count` and `total`
fields. Now we need to add those `next`, `previous`, `first` and `last` links. And
since the response is entirely created via this `PaginatedCollection` class, this
is simple: just add a new `private $_links = array();`. 

To actually add links, throw in a new function called `public function addLink()`.
This should receive two arguments, the `$ref` - which is the *name* of the link, like
`first` or `last` - and the actual `$url`. Add the link with `$this_links[$ref] = $url;`.
And that's all we need to do for this class: head to the controller.

Every link will point to the same route, just with a different `page` query parameter.
The route to this controller doesn't have a name yet, so give it one:
`api_programmers_collection`. Copy that new name and set it to a `$route` variable.

Next, create `$routeParams`: this will hold any parameters that need to be filled
in for the route - meaning the curly brace parts in the route's path. This route
doesn't have any, so set it to an empty array. We're already setting up thise code
to be reusable for other paginated responses.

Since we need generate *four* links, create a new anonymous function to help out
with this: `$createLinkUrl = function()`. Give it one argument `$targetPage`. Also,
add `use` for `$route` and `$routeParams` so we can access those inside. To generate
the URL, use the normal `return $this->generateURL()` passing it the `$route` and an
`array_merge()` of any `routeParams` with a new `page` key. Since there's no `{page}`
routing wildcard, the router will add a `?page=` query parameter to the end, exactly
how we want it to.

Ok, add the first link with `$paginatedCollection->addLink()`. Call the link `self`
and use `$page` to point to the current page. It might seem silly to link to *this* page,
but it's a pretty standard thing to do.

Copy this line and paste it twice. Name the second link `first` instead of `self`
and point this to page 1. Name the third link `last` and have it generate a URL to
the last page: `$pagerfanta->getNbPages()`.

Great! The last two links are `next` and `previous`.

But wait: we don't *always* have a next or previous page: these should be conditional.
Create an if statement: `if($pagerfanta->hasNextPage())` well, then, of course we
want to generate a link to `$pagerfanta->getNextPage()` that's called `next`.

Do this same thing for the `previous` page. `if($pagerfanta->hasPreviousPage())`,
then `getPreviousPage()` and call that link `prev`. Phew!

With some luck, the test should pass. Rerun it aaaannnddd perfect! This is pretty
cool: the tests actually *follow* those links: walking from page 1 to page 2 to page
3  and asserting things along the way.

## Link rels (self, first, etc)

One last things about the keys `self`, `first`, `last`, `next` and `prev`; these
are called the `rels` of the link and their purpose is to identify the *meaning*
of the link, just like how a link's *text* on the web tells us what's on the other
side of it.

So as long as a client using our API understands that `first` means the first page
of results and `next` means the next page of results, you can communicate the significance
of what those links are. 

And actually, these five links rels are official IANA rels. Yep, it's one of those
many standards you run into with REST. The IANA maintains a list of official link
rels that you should use when you can. Why? Because if everyone used these same
links for pagination, understanding API's would be easier and more consistent.

We *are* going to talk about links a lot more in a future episode - including all
those buzzwords like hypermedia and HATEOAS. So sit tight.

## A Re-Usable Pagination System

Since pagination is consistent, I *really* want to organize my code so that pagination
is *effortless* in the future: this still takes too many lines of code.

Create a new service. Inside of the `Pagination/` directory, create a new PHP class
called `PaginationFactory`. There, add a new `public function createCollection()`
method: this will create the *entire* final `PaginatedCollection` object for some
collection resource. To do this, we'll need to pass it a few things, starting with
the `$qb` and the `$request` object. We'll use that to find the *current* page. The
method will also need to know the route for the links and any `$routeParams` it needs.

Go back to `ProgrammerController`, copy the logic, remove it and put it into `PaginationFactory`. 
Add the missing `use` statements: I'll let these autocomplete the classes `DoctrineORMAdapter`
and `Pagerfanta`. Delete the `$route` and `$routeParams` variables because those
are passed as arguments. Delete the `$qb` variable for the same reason. In fact,
move that back to `ProgrammerController` because we'll want it in a minute. 

The only other problem here is `$this->generateUrl`: that method does *not* exist
outside of the controller. But since we *do* need to generate URLs, we need the router.
Add a `__construct()` function at the top with and the `RouterInterface` as an argument.
I'll use the alt+enter [PHPStorm shortcut](http://knpuniversity.com/screencast/phpstorm/service-shortcuts#generating-constructor-properties)
to create and set that property and then clean up some extra lines.

Back in the method, change `$this->generateUrl()` to `$this->router->generate()`.
Our work in this class is done. To use this class, register it as as service in
`app/config/services.yml`. Add a new service called `pagination_factory` and set
it to the class `PaginationFactory` class. For `arguments`, we only have one: `@router`.

Copy the service name and go find `ProgrammerController` to hook this all up.
Now, just use `$paginatedCollection = $this->get('pagination_factory')->createCollection()`
and pass it the 4 arguments: `$qb`, the `$request`, the route name - `api_programmers_collection`
and the route params. Actually, most of the time you won't have route params, so
head back into `PaginationFactory` and make that argument optional by defaulting
it to an empty array.

Now, PhpStorm *should* be happy... but it's still not! It looks more like someone
stole it's ice cream. Ah, because I forgot to `return $paginatedCollection` in
`PaginationFactory`. PhpStorm was complaining that `createCollection()` didn't look
like it returned anything... and it was right!

Run the test to see if we broke anything:

```bash
./bin/phpunit -c app --filter filterGETProgrammersCollectionPaginated
```

And it *still* passes! If you want some sweet pagination, just create a `QueryBuilder`,
pass it into the `PaginationFactory`, pass that to `createApiResponse` and then go
celebrate.
