# LexikJWTAuthenticationBundle

Google for `LexikJWTAuthenticationBundle`. This bundle is going to make creating and
validating JSON web tokens as much fun as eating ice cream. Click to read the
documentation. And now, you guys know the drill. Copy the library name from the
`composer require` line and run:

```bash
composer require lexik/jwt-authentication-bundle
```

***TIP
Version 2 of this bundle is now out! Not much has changed, but we'll tell you
when something is different!
***

While we're waiting for Jordi, I mean Composer to download that for us, let's keep
busy. Copy the new bundle line and put that into `AppKernel`:

[[[ code('331bd601b2') ]]]

Great!

## Generating the Public and Private Key

Our first goal is to write some code that can take an array of information - like
a user's username - and turn that into a JSON web token. This bundle gives us a
really handy service to do that. 

But before we can use it, we need to generate a public and private key. The private,
or secret key, will be used to *sign* the JSON web tokens. And no matter what the FBI
says, this must stay private: if someone else gets it, they'll be able to create
*new* JSON web tokens with whatever information they want - like with someone else's
username to gain access to their account. 

Copy the first line, head to the terminal and wait for Composer to finish all its
thinking. Come on Jordi! Don't worry about the error: this bundle has some required
configuration that we're *about* to provide.

First, make a new directory to hold the keys:

```bash
mkdir var/jwt
```

Next, copy the second line to create a private key, but change its path to the
`var/jwt` directory:

```bash
openssl genrsa -out var/jwt/private.pem -aes256 4096
```

This asks you for a password - give it one! It adds another layer of security in case
somebody gets your private key. I'll use `happyapi`. Perfect!

Last step: copy the final line and remove `app` at the beginning and the end to point
to the `var/jwt` directory:

```bash
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

Type in the password you just set. This creates a *public* key. It'll be used to
*verify* that a JWT hasn't been tampered with. It's not private, but you probably
won't need to share it, unless someone else - or some other app - needs to *also*
verify that a JWT we created is valid.

We now have a `private.pem` and a `public.pem`. You probably will *not* want to commit
these to your repository: the private key needs to stay secret. But there's good news!
You can create a key pair to use locally and then generate a totally different key
pair on production when you deploy. They don't need to be the same. Just don't change
the keys on production: that will invalidate any existing JSON web tokens that your
clients have.

## Configuring the Bundle

Ok, last step: tell the bundle about our keys. Copy the configuration from the docs
and open up `app/config/config.yml`. Paste this at the bottom:

[[[ code('da5ca87bc3') ]]]

Instead of using all these fancy parameters, it's fine to set the path directly:
`private_key_path: %kernel.root_dir%` - that's the `app/` directory - `/../var/jwt/private.pem`.
Do the same for the public key, with `public.pem`. Set the `token_ttl` to whatever
you want: I'll use 3600: this means every token will be valid for only 1 hour.

Finally, open `parameters.yml` and add the `jwt_key_pass_phrase`, which for me is
`happyapi`. Don't forget to add an empty setting also in `parameters.yml.dist`
for future developers:

[[[ code('78111e8f4b') ]]]

Phew! That's it! We had to generate a public and private key, but now, life is going
to be sweet. Run:

```bash
bin/console debug:container jwt
```

Select `lexik_jwt_authentication.jwt_encoder`. This is our new best friend for generating
JSON web tokens.
