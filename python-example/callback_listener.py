"""
This script starts a local server to listen for the callback from the IVAO SSO.

When the callback is received, the server will print the authorization code to the console.
This is an example of how to use the OAuth2 class to authenticate with the IVAO SSO.
Please refer to the IVAO API documentation for more information on how to use the authorization code to get an access token: https://api.ivao.aero/
For OAuth2 documentation, see https://oauth.net/2/


To run this script, you need to explicitly install flask[async] in order for it to work. (pip install 'flask[async]')

This script is not intended to be used in a production environment, it is only a demonstration of how to use the OAuth2 class to authenticate with the IVAO SSO.

Functions
---------
async callback() -> str
    This function is called when the callback is received from the IVAO SSO.
    It will print the authorization code to the console and return a message to the user in the browser.

async start_server() -> None
    This function starts the server to listen for the callback.

async get_authorization_code() -> str
    This function starts the server and gets the authorization code from the user.

async main() -> None
    This function gets the authorization code from the user and prints it to the console.
"""

import os, asyncio, traceback
from flask import Flask, request
from oauth2 import OAuth2
from dotenv import load_dotenv
load_dotenv()

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
CLIENT_STATE = os.getenv("STATE")
OPENID_URL = os.getenv("OPENID_URL")
REDIRECT_URI = os.getenv("REDIRECT_URI")

oauth2 = OAuth2(CLIENT_ID, CLIENT_SECRET, CLIENT_STATE, OPENID_URL, REDIRECT_URI)

app = Flask(__name__)
authorization_code = None

@app.route('/')
async def callback() -> str:
    """
    This function is called when the callback is received from the IVAO SSO.
    It will print the authorization code to the console and return a message to the user in the browser.

    Returns
    -------
    Result: `str`
        A message to the user in the browser either containing the error or the authorization code and the user's information.
    """
    global authorization_code
    authorization_code = request.args.get('code')
    authorization_code = authorization_code.split(' ')[0]
    # print(f'Authorization code: {authorization_code}')
    try:
        userinfo = await oauth2.getUserInfo(authtoken=authorization_code)
        success = f'Authentication successful, you can close this tab.\nYour authorization code is:\n{authorization_code}\n'
        user = f'Your user info:\n{userinfo}\n'
        msg = success + user
        return msg
    except Exception as e:
        traceback.print_exc()
        return f'Error occurred: {e}'

async def start_server() -> None:
    """
    This function starts the server to listen for the callback.
    """
    app.run(debug=False, port=3000)

async def get_authorization_code() -> str:
    """
    This function starts the server and gets the authorization code from the user.

    Returns
    -------
    Authorization code: `str`
        The authorization code received from the user.
    """
    global authorization_code
    asyncio.create_task(start_server())
    sso_url = oauth2.get_sso_url()
    print(f'Please visit this URL to authenticate: {sso_url}')
    while authorization_code is None:
        await asyncio.sleep(0.1)
    return authorization_code

async def main() -> None:
    """
    This function gets the authorization code from the user and prints it to the console.

    Returns
    -------
    None
    """
    authorization_code = await get_authorization_code()
    print(f'Authorization code: {authorization_code}')

if __name__ == '__main__':
    """
    This script starts the main function as an asyncio task.
    """
    asyncio.run(main())