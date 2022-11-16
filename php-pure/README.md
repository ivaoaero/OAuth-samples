# PHP Pure Code Examples

## Authenticate a user
_File `user.php`_

The flow is described the following documentation : [Server-Side Example Flow](https://www.oauth.com/oauth2-servers/server-side-apps/example-flow/). We are not going to use the PKCE code challenge since it's not mandatory for server-side applications.

### Local Testing 
Do not rename this file since `http(s)://localhost:8000/user.php` is an approved redirect URL for the test application. (cf. Main README.md)

Make sure to be inside the `php-pure` folder and run the following command to start a PHP server : `php -S localhost:8000`. 

Then you should be able to open [http://localhost:8000/user.php](http://localhost:8000/user.php) in your browser.

Here are the details steps that you'll find the code : 

### 1. Create a redirect link
You need to redirect your visitor (automatically or after a click on a link) to a URL with all the details describes in the documentation.

### 2. User is redirected back to your website
In the redirect URL you specified, the user will have 2 params : `code` and `state` that you'll need to use in order to get an access token. 

**Important** : The `code` is valid only 15 seconds, so you need to exchange it immediately for an access_token!

### 3. Exchange the authorization code for an access token

Make an POST request on `https://api.ivao.aero/v2/oauth/token` with the following JSON payload to get your access token (valid 1h)
```json
{
    "grant_type": "authorization_code",
    "code": "CODE_FROM_URL",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "redirect_uri": "YOUR_REDIRECT_URL"
}
```

### 4. Make an API call : 
Now that you have your access token with you, you can use it to call any API without specifying any API key. Just add the token in the `Authorization` header in your request with the following value : `Bearer YOUR_ACCESS_TOKEN`. 

For example, you can query `https://api.ivao.aero/v2/users/me` that will return you all information about the logged in user.

### 5. Enjoy !

## Authenticate as an application
_File `client-credentials.php`_

In a goal to follow industry best-practices, we are trying to get rid of API keys and are asking you to use your OAuth credentials on the API calls you make on our endpoints.

<ins>**IMPORTANT**</ins> : Please make the token request (Step 1) from your backend, never send your Client Secret in the frontend or any user will be able to steal it and use to login as your application (it's like your password, has to stay private).

### 1. Get the Access Token :
Make an POST request on `https://api.ivao.aero/v2/oauth/token` with the following JSON payload to get your access token (valid 1h)
```json
{
    "grant_type": "client_credentials",
    "client_id": "YOUR_CLIENT_ID",
    "client_secret": "YOUR_CLIENT_SECRET",
    "scope": "profile tracker training flight_plans:read" // Optional
}
```

### 2. Make an API call : 
Now that you have your access token with you, you can use it to call any API without specifying any API key. Just add the token in the `Authorization` header in your request with the following value : `Bearer YOUR_ACCESS_TOKEN`

### 3. Enjoy !