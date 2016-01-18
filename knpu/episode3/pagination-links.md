# Pagination Links

The response *is* returning a paginated list, and it even has extra `count` and `total`
fields. Now we need to add those `next`, `previous`, `first` and `last` links. And
since the response is entirely created via this `PaginatedCollection` class, this
is simple: just add a new `private $_links = array();` property.

## Creating and Setting the Links

To actually add links, create a new function called `public function addLink()`
that has two arguments: the `$ref` - that's the *name* of the link, like
`first` or `last` - and the `$url`. Add the link with `$this->_links[$ref] = $url;`.
Great - now head back to the controller.

Every link will point to the same route, but with a different `page` query parameter.
The route to this controller doesn't have a name yet, so give it one:
`api_programmers_collection`. Copy that name and set it to a `$route` variable.

Next, create `$routeParams`: this will hold any wildcards that need to be passed
to the route - meaning the curly brace parts in its path. This route doesn't have
any, so set leave it empty. We're already setting things up to be reusable for other
paginated responses.

Since we need to generate *four* links, create an anonymous function to help out
with this: `$createLinkUrl = function()`. Give it one argument `$targetPage`. Also,
add `use` for `$route` and `$routeParams` so we can access those inside. To generate
the URL, use the normal `return $this->generateURL()` passing it the `$route` and an
`array_merge()` of any `routeParams` with a new `page` key. Since there's no `{page}`
routing wildcard, the router will add a `?page=` query parameter to the end, exactly
how we want it to.

Sweet! Add the first link with `$paginatedCollection->addLink()`. Call this link `self`
and use `$page` to point to the *current* page. It might seem silly to link to *this*
page, but it's a pretty standard thing to do.

Copy this line and paste it twice. Name the second link `first` instead of `self`
and point this to page 1. Name the third link `last` and have it generate a URL to
the last page: `$pagerfanta->getNbPages()`.

The last two links are `next` and `previous`... but wait! We don't *always* have
a next or previous page: these should be conditional. Add: `if($pagerfanta->hasNextPage())`,
well, then, of course we want to generate a link to `$pagerfanta->getNextPage()`
that's called `next`.

Do this same thing for the `previous` page. `if($pagerfanta->hasPreviousPage())`,
then `getPreviousPage()` and call that link `prev`. Phew!

With some luck, the test should pass. Rerun it aaaannnddd perfect! This is pretty
cool: the tests actually *follow* those links: walking from page 1 to page 2 to page
3 and asserting things along the way.

## Link rels (self, first, etc)

The link keys - `self`, `first`, `last`, `next` and `prev` are actually called link
`rels`, or relations. They have a very important purpose: to explain the *meaning*
of the link. On the web, the link's text tells us what that link points to. In an
API, the "rel" does that job.

In other words, as long as our API client understands `first` means the first page
of results and `next` means the next page of results, you can communicate the significance
of what those links are. 

And you know what else? I *didn't* just invent these link rels. They're super-official
IANA rels - an organization that tries to standardize some of this stuff. Why is
that cool? Because if everyone used these same links for pagination, understanding
API's would be easier and more consistent.

We *are* going to talk about links a lot more in a future episode - including all
those buzzwords like hypermedia and HATEOAS. So sit tight.
