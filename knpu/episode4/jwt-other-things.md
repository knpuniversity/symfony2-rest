# JWT: Other Things to Think about

Mostly, that's it! JWT authentication is pretty cool: create an endpoint
to *fetch* a token and an authenticator to check if that token is valid. With the
error handling we added, this is a *really* robust system.

But, there are a few other things I want you to think about: things that *you* may
want to consider for your situation.

## Adding Scopes/Roles to the Token

First, when we created our JWT, we put the username inside of it. Later, we used
that to query for the User object.

But, you can put *any* information in your token. In fact, you could also include
"scopes" - or "roles" to use a more Symfony-ish word - inside your token. Also,
nobody is forcing your authenticator to load a user from the database. To get really
crazy, you could decode the token and create some new, non-entity `User` object,
and populate it entirely from the information inside of that token.

And really, not everyone issues tokens that are related to a specific user in their
system. Sometimes, tokens are more like a package of permissions that describe what
an API client can and can't do. This is a *powerful* idea.

## OAuth versus JWT

And what about OAuth? If you've watched our [OAuth tutorial](knpuniversity.com/screencast/oauth),
then you remember that OAuth is just a mechanism for securely delivering a token
to an API client. You may or may not need OAuth for your app, but if you *do* use
it, you still have the option to use JSON web tokens as your bearer, or access tokens.
It's not an OAuth versus JWT thing: each accomplishes different goals.

## Refresh Tokens

Finally, let's talk refresh tokens. In our app, we gave our tokens a lifetime of
1 hour. You see, JWT's aren't supposed to last forever. If you need them to, you
might choose to issue a refresh token along with your normal access token. Then
later, an API client could send the refresh token to the server and exchange it for
a new JWT access token. Implementing this is pretty easy: it involves creating an
extra token and an endpoint for exchanging it later. Auth0 - a leader in JWT - has
a nice blog post about it: https://auth0.com/docs/refresh-token.

Ok! If you have any questions, let me know. I know this stuff can be crazy confusing!
Do your best to not overcomplicate things.

And as always, I'll see you guys next time.
