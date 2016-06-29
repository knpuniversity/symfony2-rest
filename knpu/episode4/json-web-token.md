# JSON Web Tokens (are awesome)

How does authentication normally work on the web? Usually, after we send our
username and password, a cookie is returned to us. Then, on every request after,
we send that cookie back to the server: the cookie is delicious, and identifies who
we are, it's our *key* to the app. The server *eats* that cookie, I mean reads that
cookie, and looks it up in some database to figure out who we are.

## How all (most) API Authentication Works

Guess what? An API isn't much different. One way or another, an API client will
obtain a unique *token*, which - like the cookie - acts as their *key* to the
API. On every request, the client will send this token and the server will use that
token to figure out *who* the client is and *what* they're allowed to do.

How does it do that? Typically, the server will have a database of tokens. If I send
the token `I<3cookies`, it can query to see if that's a valid token *and* to find out
what information might be attached to it - like my user id, or even a list of permissions
or *scopes*.

By the way, some of you might be wondering how OAuth fits into all of this. Well,
OAuth is basically just a pattern for *how* your API client *gets* the token in
the first place. And it's not the only method for obtaining tokens - we'll use a
simpler method. If OAuth still befuddles you, watch our [OAuth2 Tutorial][1].
I'll mention OAuth a few more times, but mostly - stop thinking about it!

Anyways, that's token authentication in a nut shell: you pass around a secret token
string instead of your username and password.

## A Better way? JSON Web Tokens

But what if we could create a simpler system? What if the API client could simply
send us their user ID - like `123` - on each request, instead of a token. Well,
that would be awesome! Our application could just read that number, instead of needing
to keep a big database of tokens and what they mean.

Alas, we can't do that. Because then *anyone* could send *any* user ID
and easily authenticate as other users in the system. Right? Actually, no! We
*can* do this.

In your browser, open [jwt.io][2]: the main website for JSON web tokens.
These are the key to my dream. Basically, a JSON web token is nothing more than a
big JSON string that contains whatever data you want to put into it - like a user's
id or their favorite color. But then, the JSON is cryptographically signed and encoded
to create a *new* string that doesn't look like JSON at all. This is what a JSON
web token actually looks like.

But wait! JSON web tokens are encoded, but *anyone* can read them: they're easily
decoded. This means their information is *not* private: you would never put something
secret inside a JSON web token, like a credit card number - because - it turns out -
anyone can read what's inside a JSON web token.

But here's the key: *nobody* can *modify* a JSON web token without us knowing. So
if I give you a JSON web token that says your user ID is `123`, someone else *could*
steal this token and use it to authenticate as you. But, they *cannot* change the
user ID to something else. If they do, we'll know the token has been tampered with.

That's it! JSON web tokens allow us to create tokens that actually *contain* information,
instead of using random strings that require us to store and lookup the meaning of
those strings on the server. It makes life simpler.

Oh, and by the way - once you eventually deploy your API, make sure it only works
over SSL. No matter how you do authentication, tokens can be stolen. So, use HTTPS!

Now that we know why JSON web tokens - or JWT - rock my world, let's use them!


[1]: http://knpuniversity.com/screencast/oauth
[2]: http://jwt.io
