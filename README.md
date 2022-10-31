# IVAO OAuth2 Login Code Samples

## General comments
### Description
This repository contains some code samples on how to authenticate IVAO users with your systems. Once authenticated, you can make API requests on their behalf, but this is not covered here.

### Standard 
We will provide some examples and suggestions in this repo, but all the login flow complies with OAuth 2.0 standards.
If any 3-rd party tools you want to use support OAuth login, it should work out of the box.
If you have any issues or notice something unexpected happening on our end, please email us at devops[at]ivao.aero & wdm[at]ivao.aero.

### Disclaimer
As much as we would like to help each and every one of you make your systems work with our infrastructure, please understand that we currently don't have the manpower to support your technical issues. If you need any help, use the official support channels on the Community or Staff server, and other developers will try to assist you there.

## How do I implement OAuth?

### Needed information
To use the OAuth flow, you'll need to email us at devops[at]ivao.aero & wdm[at]ivao.aero with the following data : 

- Scope usage: Are you going to use this access for a divisional tool, a third-party tool, or personal use? 
- Valid email address: We need an address at which we can contact you at any point for the following reasons :
  - Abusive/Incorrect use: If we notice that you are misusing the login system, we'll contact you to sort this out before having to suspend your access.
  - Login API changes: If we have to introduce some breaking changes or new features, we want to inform you beforehand so you can prepare the migration.
- Redirect URLs: We need a complete list of URLs that the users will be allowed to be redirected to. If you have any changes, please let us know if you have any changes so we can allow or revoke some URLs

### Application data
Once your request is approved, you will be given a CLIENT_ID and a CLIENT_SECRET (obviously has to stay private) which you can use to authenticate the users.

If you think it could be possible your Client Secret token was leaked, please get in touch with us immediately at devops[at]ivao.aero & wdm[at]ivao.aero

### Test Data
If you want to test out the OAuth flow locally before requesting production data, here is the data you can use to test : 
 - Client Id: `57b2d957-38ff-4d1e-8d8f-7e5aa8d0d5fe`
 - Client Secret : `VUFqej5bLDOBngOtUcQCF97U1o7MQDbu`,
 - Redirect URLs you can : 
   - http://dev.ivao.aero
   - http://localhost
   - https://dev.ivao.aero:3000
   - http://localhost:3000
   - https://localhost:3000

_dev.ivao.aero is supposed to point to 127.0.0.1, if not, create a local record [How to edit your host file](https://www.siteground.com/kb/hosts-file/)_

## Documentation
We will not explain how to implement OAuth flow here because it's a standardized process and already well-documented. 

Here are some links you can use : 
 - [Server Side Apps](https://www.oauth.com/oauth2-servers/server-side-apps/)
 - [Single Page Apps](https://www.oauth.com/oauth2-servers/single-page-apps/)
 - [Main Links](https://www.oauth.com/#in-page)
 - [IVAO OpenID Informations](https://api-stage.ivao.aero/.well-known/openid-configuration)

## Issues
If you need any help, try using the official support channels on the Community or Staff servers, where other developers will try to assist you.

If you believe our documentation isn't clear enough, please open an issue or a pull request to let us know so we can improve this for all future users.

## Contribution
Feel free to open a Pull Request to add/fix a code example. The more code we have, the easier it will be for everyone to use the OAuth login flow. 

We really appreciate any contributions made ;)

## Contact
If you have any questions or want to report a technical issue on our side, feel free to contact us at devops[at]ivao.aero & wdm[at]ivao.aero