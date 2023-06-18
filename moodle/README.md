# Moodle IVAO OAuth integration

## Plugins

There are 2 plugins available to authenticate IVAO users on moodle : 
 - `oauth2`: This plugin is already built into moodle default installation and is recommended by IVAO as is has more configuration options
 - [moodle-auth_oidc](https://github.com/Microsoft/moodle-auth_oidc): Developed by Microsoft, this plugin allows the use of OpenID to authenticate users. It has less configuration options and it oriented towards Azure and Office 365 OpenID servers.

# OAuth2

# Installation

As the plugin is already part of the defaut installation, you only need to activate it by going to [Site administration > Plugins > Authentication > Manage authentication](http://moodle.local/admin/settings.php?section=manageauths) and click on the Eye icon of the OAuth 2 line so it activates the plugin.

## Configuration

Please refer to the [official documentation](https://docs.moodle.org/402/en/OAuth_2_services) to setup the integration. 

### Issuer 

Create a new Custom OAuth 2 Service in [Site administration > Server > Server > OAuth 2 services](http://moodle.local/admin/tool/oauth2/issuers.php)

Here are the field values to enter : 
 - **Name**: Whatever name you want to display on the login page. Like "_Login with IVAO_"
 - **Client ID**: Client ID value provided by IVAO
 - **Client Secret**: Client Secret value provided by IVAO
 - **Authenticate token requests via HTTP headers**: This option is supported by IVAO OAuth system and can be used if you want, but is not mandatory
 - **Scopes**: Only `profile` and `email` are required but you can keep `openid` if you want
 - **Additional Parameters**: No additional parameters are required for this integration to work
 - **Service Base URL**: Use `https://api.ivao.aero` for Moodle to automatically fetch all the endpoints
 - **Login Domains**: Keep it empty to allow all users to login with IVAO
 - **Logo URL**: Use any IVAO Logo in respect to the [IVAO Branding Guidelines](https://brand.ivao.aero/logo/). We suggest using `https://static.ivao.aero/favicon.ico`
 - **Show on login Page**: Please tick this box to display the button on your Moodle's login page
 - **Require email verification**: Please **<u>DO NOT TICK</u>** this checkbox as you don't need to validate email adresses provided by IVAO. 
   - If this option is not present in your configuration, it measn that you are running a version lower than `3.11.6`. Please refer to [this issue](https://tracker.moodle.org/browse/MDL-67802) to fix it by manually applying [this commit](https://github.com/mattporritt/moodle/commit/07d40a91ee2e4e87ee4aed4f66d4295efabd0a54)

### Endpoints

Endpoints should be setup correctly if you provided the proper Service Base URL in the Issuer configuration. 
Here are the values that you should have (taken from [here](https://api.ivao.aero/.well-known/openid-configuration)) : 
 - **authorization_endpoint**: `https://sso.ivao.aero/authorize`
 - **token_endpoint**: `https://api.ivao.aero/v2/oauth/token`
 - **userinfo_endpoint**: `https://api.ivao.aero/v2/users/me`
 - **revocation_endpoint**: `https://api.ivao.aero/v2/oauth/token/revoke`

### User field mappings

This is where you configure which fields from IVAO API are used to populate the Moodle User. 

The available fields can be found on [IVAO API Documentation](https://api.ivao.aero/docs) under "CORE Documentation" in the left-sidebar and then scrolldown to the `/v2/users/me` endpoint (In the `users` group).

Recommended mappings are : 
 - `id` -> `username`
 - `firstName` -> `firstname`
 - `lastName` -> `lastname`
 - `email` -> `email`
 - `divisionId` -> `institution`
 - `languageId` -> `lang`
 - `id` -> `idnumber`

You can also use `publicNickname` to have the following format: `Firstname (VID)` 

You might also want to lock those fields so the users won't be able to edit them on their Moodle profile page. Go to [Side Administration > Plugins > Authentication -> OAuth 2](http://moodle.local/admin/settings.php?section=authsettingoauth2) and set all the fields mentionned above to `Locked`.

## Known issues

### User information not synced after 1st login 

This issue was fixed in the `3.7` version. Please refer to [this issue](https://tracker.moodle.org/browse/MDL-61767) to apply [these commits](https://github.com/andrewnicols/moodle/compare/44890bd738...MDL-61767-master) manually.


# OpenID 

## Installation

Install the plugin from [the official plugin page](https://moodle.org/plugins/auth_oidc). 

After installation it will directly prompt you with the plugin's configuration options that we are going to cover later in this document.

After configuration, you need to activate it by going to [Site administration > Plugins > Authentication > Manage authentication](http://moodle.local/admin/settings.php?section=manageauths) and click on the Eye icon of the OpenID Connect line so it activates the plugin.

## Configuration
### IdP and authentication
Here are the IVAO-specific settings to enter under [Site administration > Plugins > Authentication > OpenID Connect > IdP and authentication](http://moodle.local/auth/oidc/manageapplication.php):
 - **Identity Provider (IdP) Type**: `Other`
 - **Application Id**: Client ID provided by IVAO
 - **Client authentication method**: `Secret`
 - **Client Secret**: Client Secret provided by IVAO
 - **Client certificate private key**: Not needed
 - **Client certificate public key**: Not needed
 - **Authorization Endpoint**: `https://sso.ivao.aero/authorize`
 - **Token Endpoint**: `https://api.ivao.aero/v2/oauth/token`
 - **Resource**: Not implemented by IVAO, you can use `ivao` as a placeholder 
 - **Scope**: You will need `openid`, `profile` and `email` at least.

### Other options
Here are the options to configure under [Site administration > Plugins > Authentication > OpenID Connect > Other options](http://moodle.local/admin/settings.php?section=authsettingoidc):
- **Redirect URL**: You can't edit this field, it is just an information of the URL used to redirect the users after completing IVAO SSO process. It needs to be added to our IVAO OAuth Application settings
- **IdP and authentication**: Covered in the previous section
- **Force redirect**: This option is recommended so users don't get confused with Moodle own authentication form and logic.
- **Auto-Append**: Not needed
- **Domain Hint**: Not needed
- **Login Flow**: `Authorization Code Flow` is the one supported by IVAO OAuth and also recommended by the plugin
- **User Restrictions**: Not needed if you want to allow all users to login with IVAO
- **Single Sign Out**: This option is not required and is not recommended by IVAO
- **IdP Logout Endpoint**: If you choose to use the option above, here is the URL to use: `https://sso.ivao.aero/logout`
- **Front-channel Logout URL**: Not supported by IVAO
- **Provider Display Name**: Name on the button to use OpenID Connect. We suggest "_Login with IVAO_"
- **Custom Icon**: Use any IVAO Logo in respect to the [IVAO Branding Guidelines](https://brand.ivao.aero/logo/). We suggest using `https://static.ivao.aero/favicon.ico`

### Field mappings
Here are the mappings to set under [Site administration > Plugins > Authentication > OpenID Connect > Field mappings](http://moodle.local/admin/settings.php?section=auth_oidc_field_mapping):
- `First Name` -> `Given Name`
- `Last Name` -> `Surname`
- `Email address` -> `Email`
- `ID Number` -> `Object ID` (please check the Known Issues below)

Keep in mind that by default the Moodle user's username will be his IVAO VID

You might also want to lock the fields above so the users won't be able to edit them on their Moodle profile.

We recommand setting `Update Local` to `On every login` for all the fields above in case the user details changed on the IVAO side.

## Know issues
### User VID not mapped
If you want to use the user's VID in your Moodle User mapping, [please refer to this issue](https://github.com/microsoft/o365-moodle/issues/2295)

# Support

If you encounter any issue, please contact us on the Discord channels or at [web@ivao.aero](mailto:web@ivao.aero)