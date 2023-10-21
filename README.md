# IVAO OAuth2 Code Samples

## Availability
Currently, the IVAO OAuth2 API is only available for IVAO divisions.
We are planning on extending this feature to Virtual Airlines, Partners and 3rd party developers soon.

## General comments
### Description
This repository contains some code samples on how to authenticate IVAO users and applications with our systems. Once authenticated, you can make API requests on their behalf.

### Standard 
We will provide some examples and suggestions in this repo, but all the login flow complies with OAuth 2.0 standards.

Any 3-rd party tools supporting OAuth login should work out of the box.

If you have any issues or notice something unexpected happening on our end, please email us at [web@ivao.aero](mailto:web@ivao.aero).

### Disclaimer
As much as we would like to help each and every one of you, please understand that we currently don't have the manpower to support all technical issues you migth encounter.

If you need any help, please use the official support channels on the Community or Staff server, where other developers will try to assist you.

## Documentation
We will not explain how the OAuth flow works here because it's a standardized process and already well-documented. 

Here are some links you can use : 
 - [Server Side Apps](https://www.oauth.com/oauth2-servers/server-side-apps/)
 - [Single Page Apps](https://www.oauth.com/oauth2-servers/single-page-apps/)
 - [Main Links](https://www.oauth.com/#in-page)
 - [IVAO OpenID Information](https://api.ivao.aero/.well-known/openid-configuration)
 - [IVAO OAuth Scopes](https://wiki.ivao.aero/en/home/devops/api/oauth-scopes)

## Examples provided in this repository

### ReactJS with 3rd-party libraries
Example can be found [here (reactjs-with-lib)](https://github.com/ivaoaero/OAuth-samples/tree/main/reactjs-with-lib)

This example how to authenticate users on frontend-only applications without the need of a backend. 

_PS: This is how IVAO 2.0 websites (Webeye, FPL, Tracker) are working_

### PHP without any libraries
Example can be found [here (php-pure)](https://github.com/ivaoaero/OAuth-samples/tree/main/php-pure)

This example shows both how to authenticate a user visiting your website as well as how to authenticate your backend application without any user interaction.

### Laravel without Socialite
Example can be found [here (laravel-pure)](https://github.com/ivaoaero/OAuth-samples/tree/main/laravel-pure)

This example shows both how to authenticate a user visiting your website and how to store all his details in your database.

### Moodle
Documentation can be found [here (moodle)](https://github.com/ivaoaero/OAuth-samples/tree/main/moodle)

## How do I implement OAuth with IVAO APIs?

### Needed information
To use the OAuth flow, you'll need to email us at [web@ivao.aero](mailto:web@ivao.aero) with the following pieces of information : 

- Scope usage: Are you going to use this access for a divisional tool, a third-party tool, a virtual-airline tool or personal use? 
- Valid email address: We need an address at which we can contact you at any point for the following reasons :
  - Abusive/Incorrect use: If we notice that you are misusing the login system, we'll contact you to sort this out before having to suspend your access.
  - Login API changes: If we have to introduce some breaking changes or new features, we want to inform you beforehand so you can prepare the migration.
- Redirect URLs: We need a complete list of URLs that the users will be allowed to be redirected to. If you need any changes, please let us know.

### Application data
Once your request is approved, you will be given a CLIENT_ID and a CLIENT_SECRET (obviously has to stay private) which you can use to authenticate the users and your applications.

If you think it could be possible that your Client Secret token was leaked, please get in touch with us immediately at [web@ivao.aero](mailto:web@ivao.aero).

### Test Data
If you want to test out the OAuth flow locally before requesting production data, here is the data you can use to test : 
 - Client Id: `57b2d957-38ff-4d1e-8d8f-7e5aa8d0d5fe`
 - Client Secret : `VUFqej5bLDOBngOtUcQCF97U1o7MQDbu`,
 - Redirect URLs you can use: 
   - http(s)://localhost
   - http(s)://localhost/auth/callback
   - http(s)://localhost/user.php
   - http(s)://localhost:8000/auth/callback
   - http(s)://dev.ivao.aero
   - http(s)://dev.ivao.aero:3000
   - http(s)://localhost:3000

_dev.ivao.aero is supposed to point to 127.0.0.1, if not, create a local record [How to edit your host file](https://www.siteground.com/kb/hosts-file/)_

## Issues
If you need any help, try using the official support channels on the Community or Staff servers, where other developers will try to assist you.

If you believe our documentation isn't clear enough, please open an issue or a pull request to let us know so we can improve this for all future users.

## Contribution
Feel free to open a Pull Request to add/fix a code example. The more code we have, the easier it will be for everyone to use the OAuth login flow. 

We really appreciate any contributions made ;)

To keep the repository clean, we are using [commitlint](https://github.com/conventional-changelog/commitlint) that ensures that the commit messages are following the guidelines.
Please run `yarn install` and `yarn husky install` to install the hook before making any changes or your pull request might get refused. 

## Contact
If you have any questions or want to report a technical issue on our side, feel free to contact us at [web@ivao.aero](mailto:web@ivao.aero)
