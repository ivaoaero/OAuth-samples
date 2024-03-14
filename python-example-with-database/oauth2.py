"""IVAO OAuth2 module

This module contains the OAuth2 class, which is used to refresh the access token and get the user's information.
We made this module in a way that it uses a database to store the refresh tokens related to, in our case, Discord user IDs.
You can modify the database part to your own needs, but we advise you to use a database to store the refresh tokens.
For database handling, a `dbconn.py` file is also provided, containing everything needed for this module to function properly.

This file can also be imported as a module and contains the following functions:

    * refresh_token - can be called to gain a new access token and refresh token
    * getUserInfo - can be called to get the user's information (from IVAO API)

Further information can be found in the docstrings of the functions: parameters and return values.
Documentation for aiohttp can be found at https://docs.aiohttp.org/en/stable/
IVAO API documentation can be found at https://api.ivao.aero/docs at the OAUTH section, scheme at https://api.ivao.aero/docs/oauth-json

Import this file as a module like this and use the OAuth2 module:
    `from oauth2 import OAuth2`
    `oauth2 = OAuth2(CLIENTID, CLIENTSECRET, STATE)`

You should store the Client ID and Client Secret in a .env file and load them with the `dotenv` module:
    `from dotenv import load_dotenv`
    `load_dotenv()`
    `CLIENTID = os.getenv("CLIENTID")`
    `CLIENTSECRET = os.getenv("CLIENTSECRET")`
    `STATE = os.getenv("STATE")`

For support, contact: https://ivao.aero/Member.aspx?Id=677678 or drop an email to tancsics.gergely@ivao.aero / https://discord.hu.ivao.aero
"""

import aiohttp, logging, aiomysql, traceback
from datetime import datetime
from typing import Union
from dbconn import MySQLPool

async def create_mysqlpool() -> aiomysql.pool.Pool:
    """
    Creates a MySQL connection pool.

    Returns
    -------
    Database connection pool: :class:`aiomysql.pool.Pool`
        The created connection pool to the database. We can use this pool to execute queries.
    """

    pool_class: aiomysql.Connection = MySQLPool()
    return await pool_class.create_pool()

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

    Methods
    -------
    refresh_token(userid: int, mysqlpool: MySQLPool, revoke: bool = False) -> Union[dict, str]:
        Uses the /v2/oauth/token endpoint to gain a new access token and refresh token.
    getUserInfo(userid: int, mysqlpool: MySQLPool, revoke: bool = False) -> Union[dict, str]:
        Uses the /v2/users/me endpoint to get the user's information.
    """
    def __init__(self, client_id: str, client_secret: str, state: str) -> None:
        # You can either connect to an existing logger or create a new one. In that case further configuration is needed.
        self.logger = logging.getLogger('discord')
        self.CLIENT_ID = client_id
        self.CLIENT_SECRET = client_secret
        self.STATE = state
        self.logger.info("OAuth2 initialized.")

    async def refresh_token(self, userid: int, mysqlpool: MySQLPool, revoke: bool = False) -> Union[dict, str]:
        """
        Uses the /v2/oauth/token endpoint to gain a new access token and refresh token.
        If the revoke parameter is set to True, the old refresh tokens will be revoked (not recommended -> will require re-authorization via a website).
        Access tokens are valid for 30 minutes, refresh tokens are valid for 30 days, but it is easier to use a new access token every time.

        Parameters
        ----------
        userid: `int`
            The discord user id of the user to refresh the token for. This is used to get the refresh token from the database.
        mysqlpool: :class:`aiomysql.pool.Pool`
            The connection pool to the database. If you do not modify that part of the code, it'll always be `mysqlpool`.
        revoke: `bool`
            Whether to or not to revoke the old refresh tokens, defaults to False, not recommended to set to True.

        Returns
        -------
        `dict` or `str`:
            The new access and refresh tokens, insert refresh_token into database if successful.
            The error message if unsuccessful or if there was an error during the database connection part.
        """

        try:
            async with mysqlpool.acquire() as conn:
                async with conn.cursor() as cur:
                    sql_query = "SELECT refresh_token FROM user_data WHERE discord_user_id = %s" # ALWAYS use %s for strings and %d for integers. This is for security reasons and standards.
                    await cur.execute(sql_query, args=(userid,)) # Get the refresh token from the database, you can use your own scheme.
                    refreshtoken = (await cur.fetchone())[0]

        except Exception as e:
            self.logger.error(e)
            traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
            return str(e)

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
                    else:
                        pass

                    try:
                        async with mysqlpool.acquire() as conn:
                            async with conn.cursor() as cur:
                                sql_query = "UPDATE user_data SET `refresh_token` = %s, `refresh_token_date` = %s WHERE `discord_user_id` = %s" # It is useful and recommended to store the date of the refresh token, this way you can refresh it with for example a cron job. Advised to refresh every 20 days.
                                await cur.execute(sql_query, (newauthdata['refresh_token'], datetime.now().strftime("%Y-%m-%d %H:%M:%S"), userid)) # Update the refresh token in the database
                                await conn.commit()
                    except Exception as e:
                        self.logger.error(e)
                        traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
                        return str(e)

                    async with session.get("https://api.ivao.aero/v2/users/me", headers={"Authorization": "Bearer " + newauthdata['access_token']}) as data:
                        return await data.json()

                else:
                    self.logger.error(f'Error when trying to refresh token for user {userid}: {newauthdata}')
                    self.logger.error(newauthdata)
                    return newauthdata

    async def getUserInfo(self, userid: int, mysqlpool: MySQLPool, revoke: bool = False) -> Union[dict, str]:
        """
        Uses the /v2/users/me endpoint to get the user's information. Returns everything that the IVAO API returns about the user, in a raw format.
        If the access token is invalid, it will be refreshed with the refresh_token function and the new access token will be used.

        Parameters
        ----------
        userid: `int`
            The discord user id of the user to get the information for.
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
            If the access token is invalid, it will be refreshed with the refresh_token function and the new access token will be used.
            Theoritically, this will always be called, because we do not store the access token in the database, and it is not advised that this changes.
        """

        try:
            newauthdata = await self.refresh_token(userid, mysqlpool=mysqlpool, revoke=revoke)
        except Exception as e:
            self.logger.error(e)
            traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
            return str(e)

        if isinstance(newauthdata, str):
            return newauthdata

        if newauthdata.get('access_token'):
            try:
                async with mysqlpool.acquire() as conn:
                    async with conn.cursor() as cur:
                        sql_query = "UPDATE user_data SET `refresh_token` = %s WHERE `discord_user_id` = %s"
                        await cur.execute(sql_query, (newauthdata['refresh_token'], userid))
                        await conn.commit()
            except Exception as e:
                self.logger.error(e)
                traceback.print_exc() # This will print the traceback to the console, you can remove it if you want to.
                return str(e)

            async with aiohttp.ClientSession() as session:
                async with session.get("https://api.ivao.aero/v2/users/me", headers={"Authorization": "Bearer " + newauthdata['access_token']}) as data:
                    return await data.json()

        else:
            return newauthdata