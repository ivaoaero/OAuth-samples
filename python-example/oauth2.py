"""IVAO OAuth2 module

This module contains the OAuth2 class, which is used to authenticate with the IVAO Single Sign-On (SSO) system, or to refresh the access token and get the user's information.

This file can also be imported as a module and contains the following functions:

    * get_sso_url - can be called to get the Single Sign-On URL
    * refresh_token - can be called to gain a new access token and refresh token
    * getUserInfo - can be called to get the user's information (from IVAO API)

Further information can be found in the docstrings of the functions: parameters and return values.
Documentation for aiohttp can be found at https://docs.aiohttp.org/en/stable/
IVAO API documentation can be found at https://api.ivao.aero/docs at the OAUTH section, scheme at https://api.ivao.aero/docs/oauth-json

You should store the Client ID and Client Secret in a .env file and load them with the `dotenv` module:
    `from dotenv import load_dotenv`

    `load_dotenv()`
    
    `CLIENTID = os.getenv("CLIENT_ID")`
    
    `CLIENTSECRET = os.getenv("CLIENT_SECRET")`
    
    `STATE = os.getenv("STATE")`
    
    `OPENID_URL = os.getenv("OPENID_URL")`
    
    `REDIRECT_URI = os.getenv("REDIRECT_URI")`

Import this file as a module like this and use the OAuth2 module:
    `from oauth2 import OAuth2`

    `oauth2 = OAuth2(CLIENTID, CLIENTSECRET, STATE, OPENID_URL, REDIRECT_URI)`

For support, contact: https://ivao.aero/Member.aspx?Id=677678 or drop an email to tancsics.gergely@ivao.aero / https://discord.hu.ivao.aero
"""

import aiohttp, logging, traceback, urllib.request, json
from typing import Union

class OAuth2:
    """
    OAuth2 class to refresh the access token and get the user's information.
    You can generate your own client id and client secret on the following website:
    https://developers.ivao.aero

    Attributes
    ----------
    CLIENT_ID: `str`
        The client id of the OAuth application.
    CLIENT_SECRET: `str`
        The client secret of the OAuth application.
    STATE: `str`
        The state variable of the application.
    OPENID_URL: `str`
        The URL to get the OpenID data from the IVAO API.
    REDIRECT_URI: `str`
        The redirect URI of the OAuth application.

    Methods
    -------
    get_sso_url() -> str:
        Gets the Single Sign-On URL.
    refresh_token(refreshtoken: str, revoke: bool = False) -> Union[dict, str]:
        Async coro, uses the /v2/oauth/token endpoint to gain a new access token and refresh token.
    getUserInfo(userid: int, mysqlpool: MySQLPool, revoke: bool = False) -> Union[dict, str]:
        Async coro, uses the /v2/users/me endpoint to get the user's information.
    """
    def __init__(self, client_id: str, client_secret: str, state: str, openid_url: str, redirect_uri: str) -> None:
        # You can either connect to an existing logger or create a new one. In that case further configuration is needed.
        self.logger = logging.getLogger('oauth')
        self.CLIENT_ID = client_id
        self.CLIENT_SECRET = client_secret
        self.STATE = state
        self.OPENID_URL = openid_url
        self.REDIRECT_URI = redirect_uri

        self.openid_data = None
        self.logger = logging.getLogger('oauth')

        openid_result = urllib.request.urlopen(self.OPENID_URL).read().decode('utf-8')
        if openid_result is None:
            self.logger.error('Error while getting openid data')
            raise Exception('Error while getting openid data')

        self.openid_data = json.loads(openid_result)
        self.logger.info("OAuth2 initialized.")

    def get_sso_url(self) -> str:
        """
        Gets the Single Sign-On URL.

        Returns
        -------
        `str`:
            The Single Sign-On URL.
        """
        base_url = self.openid_data['authorization_endpoint']
        response_type = 'code'
        scopes = 'profile configuration email'
        sso_url = f"{base_url}?response_type={response_type}&client_id={self.CLIENT_ID}&scope={scopes}&redirect_uri={self.REDIRECT_URI}"
        return sso_url

    async def refresh_token(self, refreshtoken: str, revoke: bool = False) -> Union[dict, str]:
        """
        Uses the /v2/oauth/token endpoint to gain a new access token and refresh token for a given user.
        If the revoke parameter is set to True, the old refresh tokens will be revoked (not recommended -> will require re-authorization via a website).
        Access tokens are valid for 30 minutes, refresh tokens are valid for 30 days, but it is easier to use a new access token every time.

        Parameters
        ----------
        refreshtoken: `str`
            The refresh token of the user to get a new access token for.
        revoke: `bool`
            Whether to or not to revoke the old refresh tokens, defaults to False, not recommended to set to True.

        Returns
        -------
        `dict` or `str`:
            The new access and refresh tokens, insert refresh_token into database if successful.
            The error message if unsuccessful or if there was an error during the database connection part.
        """

        getnewauthtokenurl = 'https://api.ivao.aero/v2/oauth/token' # The URL to get a new access token and refresh token, do not change. https://api.ivao.aero/
        revokerefreshtokenurl = 'https://api.ivao.aero/v2/oauth/token/revoke' # The URL to revoke the refresh token, do not change.
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'method': 'Post'
        }

        newaccessdata = {
            'grant_type': 'refresh_token',
            'refresh_token': refreshtoken,
            'client_id': self.CLIENT_ID,
            'client_secret': self.CLIENT_SECRET,
            'state': self.STATE
        }

        async with aiohttp.ClientSession() as session:
            async with session.post(getnewauthtokenurl, headers=headers, data=newaccessdata) as newauth:
                newauthdata = await newauth.json()

                if newauthdata.get('access_token'):
                    if revoke:
                        async with session.post(revokerefreshtokenurl, headers=headers, data=revokerefreshdata) as revokerefresh:
                            revokerefreshdata = await revokerefresh.json()

                    async with session.get("https://api.ivao.aero/v2/users/me", headers={"Authorization": "Bearer " + newauthdata['access_token']}) as data:
                        return await data.json()

                else:
                    self.logger.error(newauthdata)
                    traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
                    return newauthdata

    async def getUserInfo(self, refreshtoken: str = None, authtoken: str = None, revoke: bool = False) -> Union[dict, str]:
        """
        Uses the /v2/users/me endpoint to get the user's information. Returns everything that the IVAO API returns about the user, in a raw format.
        Only provide one of the two parameters, refreshtoken or authtoken. If both are provided, the method will return an error message.

        Parameters
        ----------
        refreshtoken: `str`
            The refresh token of the user to get a new access token for.
        authtoken: `str`
            The access token of the user to get the user's information.
        revoke: `bool`
            Whether or not to revoke the old refresh token, defaults to False.

        Returns
        -------
        `dict` or `str`:
            The user's information.
            The error message if unsuccessful.
            If there was an error during the database connection part.

        Calls
        -----
        `refresh_token`:
            If the refresh token is provided, it will call the refresh_token method to get a new access token and refresh token.
        """

        if authtoken and not refreshtoken:
            try:
                async with aiohttp.ClientSession() as session:
                    async with session.get("https://api.ivao.aero/v2/users/me", headers={"Authorization": "Bearer " + authtoken}) as data:
                        return await data.json()
            except Exception as e:
                self.logger.error(e)
                traceback.print_exc()
                return str(e)

        elif refreshtoken and not authtoken:
            try:
                newauthdata = await self.refresh_token(refreshtoken, revoke)
            except Exception as e:
                self.logger.error(e)
                traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
                return str(e)

            if isinstance(newauthdata, str):
                return newauthdata

            if newauthdata.get('access_token'):
                async with aiohttp.ClientSession() as session:
                    async with session.get("https://api.ivao.aero/v2/users/me", headers={"Authorization": "Bearer " + newauthdata['access_token']}) as data:
                        return await data.json()

            else:
                return newauthdata

        else:
            return "No refresh token or access token provided, or both were provided. Please provide only one of the two."