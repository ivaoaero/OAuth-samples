# ReactJS OAuth with react-oicd-context
## Description
Here is an example of how to use a ReactJS lib to authenticate IVAO users without needing any backend.

Just install the depencies with `yarn` and start the app with `yarn dev`

## Environment configuration.
The `.env` file contains the following values : 
 - VITE_OPENID_CLIENT_ID: Your app client Id
 - VITE_OPENID_REDIRECT_URI: The url the user should be redirected to after a successful login
 - VITE_OPENID_AUTHORITY: The domain on which all OpenID values are stored. Here `https://api.ivao.aero` because the library is going to fetch [https://api.ivao.aero/.well-known/openid-configuration](https://api.ivao.aero/.well-known/openid-configuration)

No need to specify the Client Secret since it would be leaked to the user if you decide to use it and this would be a security issue.

Behind the hood the library is using a [code challenge](https://www.oauth.com/oauth2-servers/pkce/authorization-request/) to make sure the request is not forged.

## Known error messages
### Error: No matching state found in storage
Meaning you got redirected to a different domain that the one you were coming from. For example you opened the website on `http://127.0.0.1` but got redirected to `http://localhost`. Your browser didn't persist the state information across both "domains". 

To prevent this issue, please make sure the redirect URL domain provided in the config is the same as the one you are visiting the website from.