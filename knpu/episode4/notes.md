- create a test to get a 401
- require a user - make this pass!
- setup JWT Guard authentication
    - basics on JWT
    - iss, exp

    - get bundle
    - create public/private key
        mkdir var/jwt
        openssl genrsa -out var/jwt/private.pem -aes256 4096
            (happyapi)
        openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
    - configure!


- setup test to create one of these guys - make it pass!
- test for bad token
- make sure this is the right api+problem json format
- test for endpoint to *get* tokens
- hook this up (http_basic with endpoint?)
- test for bad user/pass
- mention how this is different than OAuth

NOTES
- you could use the JWT entirely to store user data,
    including roles. Use this for more granular access
- could be a shared key across many mini-apps
- mention refresh tokens, scopes, OAuth, etc
- handle auth errors - like expiration
- handling authentication for token error correctly