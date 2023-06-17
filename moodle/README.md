# Moodle IVAO OAuth integration

## Plugin to use

Please use Moodle's integrated `oauth2` plugin and not [moodle-auth_oidc (Developed my Microsoft)](https://github.com/Microsoft/moodle-auth_oidc) because the latter has some issues fetching the user details. [Please refer to this issue](https://github.com/microsoft/o365-moodle/issues/2295)

## Configuration

Please refer to the [official documentation](https://docs.moodle.org/402/en/OAuth_2_services) to setup the integration. 

### Issuer 

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
 - `firstName` -> `firstname`
 - `lastName` -> `lastname`
 - `email` -> `email`
 - `divisionId` -> `institution`
 - `languageId` -> `lang`
 - `id` -> `idnumber`

You can also use `publicNickname` to have the following format: `Firstname (VID)` 

## Known issues

### User information not synced after 1st login 

This issue was fixed in the `3.7` version. Please refer to [this issue](https://tracker.moodle.org/browse/MDL-61767) to apply [these commits](https://github.com/andrewnicols/moodle/compare/44890bd738...MDL-61767-master) manually.

## Support

If you encounter any issue, please contact us on the Discord channels or at [web@ivao.aero](mailto:web@ivao.aero)