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
async def callback() -> str:
    Asyncronous function.
    This function is called when the callback is received from the IVAO SSO.
    It will print the authorization code to the console and return a message to the user in the browser.

async def start_server(server_started: asyncio.Event) -> None:
    Asyncronous function.
    This function starts the server to listen for the callback.

def getcookie() -> dict:
    This function is used to get the cookie from the browser storage. It can be printed to the user, or used in the code as a variable.

async def main() -> None:
    Asyncronous function.
    This function gets the authorization code from the user and prints it to the console.
"""

import os, asyncio, traceback, random
from flask import Flask, request, make_response, redirect
from oauth2 import OAuth2
from dotenv import load_dotenv

load_dotenv()

CLIENT_ID = os.getenv("CLIENT_ID")
CLIENT_SECRET = os.getenv("CLIENT_SECRET")
CLIENT_STATE = os.getenv("STATE")
OPENID_URL = os.getenv("OPENID_URL")
REDIRECT_URI = os.getenv("REDIRECT_URI")
YOUR_SITE_DOMAIN = os.getenv("YOUR_SITE_DOMAIN")

app = Flask(__name__)
app.secret_key = os.urandom(24)  # Set a secret key for session management

oauth2 = OAuth2(CLIENT_ID, CLIENT_SECRET, CLIENT_STATE, OPENID_URL, REDIRECT_URI)

@app.route('/')
async def callback() -> str:
    """
    This function is called when the callback is received from the IVAO SSO.
    It will print the authorization code to the console and return a message to the user in the browser.

    Returns
    -------
    Result: `str`
        A message to the user in the browser.
    Errors/Exceptions: `str`
        An error message if an unhandled exception occurs.

    Redirects
    ---------
    Redirects the user to the login page if the authorization code is not present.
    """
    try:
        authorization_code = request.args.get('code')
        authorization_code = authorization_code.split(' ')[0]
        userinfo = await oauth2.getUserInfo(authtoken=authorization_code)
        success = f'Authentication successful, you can close this tab.\nYour authorization code is:\n{authorization_code}\n'
        user = f'Your user info:\n{userinfo}\n'
        msg = success + user
        response = make_response(msg)
        response.set_cookie('authorization_code', authorization_code, max_age=1800) # Set the cookie to expire in 30 minutes (1800 seconds)

        # This will be the actual code you want to use, containing a site restriction and secure cookie.
        # response.set_cookie('authorization_code', authorization_code, max_age=1800, domain=YOUR_SITE_DOMAIN, secure=True, httponly=True, samesite='Strict')
        return response, str(request.cookies)

    except AttributeError:
        return redirect('/login') # Redirect the user to the login page if the authorization code is not present

    except Exception as e:
        traceback.print_exc()
        return f'Error occurred: {e}'

@app.route('/login')
def login() -> str:
    """
    Redirects the user to the IVAO SSO for authentication.
    """
    return redirect(oauth2.get_sso_url())

@app.route('/userdata')
async def userdata() -> str:
    """
    Demo site to show the user the authorization code.
    Also shows the user's information if the user is authenticated.

    Returns
    -------
    Result: `str`
        A message to the user in the browser with the authorization code and user information from the IVAO API.
    """
    cookies = getcookie()
    if 'authorization_code' in cookies:
        userdata = await oauth2.getUserInfo(authtoken=cookies["authorization_code"])
        return f'You have successfully authenticated and gotten the authorization code.<br><br>Your authorization code is: {cookies["authorization_code"]}<br><br>User data:<br>{userdata}'
    else:
        return 'You have not authenticated yet. Please authenticate first by clicking the button below.<br><br><a href="/login">Authenticate</a>'

async def start_server(server_started: asyncio.Event) -> None:
    """
    This function starts the server to listen for the callback.

    Parameters
    ----------
    server_started: `asyncio.Event`
        An asyncio event to signal when the server has started.

    Returns
    -------
    None
    """
    app.run(port=3000)
    server_started.set()

def getcookie() -> dict:
    """
    This function is used to get the cookie from the browser storage printed for the user.

    Returns
    -------
    Result: `dict`
        The cookie stored in the browser.
    """

    return request.cookies

async def main() -> None:
    """
    This function starts the server and gets the authorization code from the user.

    Returns
    -------
    None
    """
    server_started = asyncio.Event()

    asyncio.create_task(start_server(server_started))
    await server_started.wait()

    await asyncio.sleep(1)

    while True:
        authorization_code = request.cookies.get('authorization_code')
        if authorization_code:
            print(f'Authorization code: {authorization_code}')
            break
        await asyncio.sleep(0.1)

if __name__ == '__main__':
    """
    This script starts the main function as an asyncio task.
    """
    asyncio.run(main())