"""Database connection handler

This file contains all of the database features that
can be necessary to operate the OAuth module (and other applications as well).
It automatically opens a database connection on startup, creates a pool
from which we can request connections. It handles pool creating,
closing, executing a single or multiple SQL statements.

It has been reworked from mysql-connector to aiomysql, which is an asynchronous
mysql connector, which is more efficient and faster than the previous implementation.

This file can also be imported as a module and contains the following
functions:

    * create_pool - creates a connection pool to the database
    * close - closes a connection to the database
    * execute - executes a single SQL statement
    * executemany - executes multiple SQL statements

Further information can be found in the docstrings of the functions: parameters and return values.
Documentation for aiomysql can be found at https://aiomysql.readthedocs.io/

Import this file as a module like this:
    `from dbconn import MySQLPool`
    `pool_class = MySQLPool()`
    `client.mysqlpool = await pool_class.create_pool()`

For support, contact: https://ivao.aero/Member.aspx?Id=677678 or drop an email to tancsics.gergely@ivao.aero / https://discord.hu.ivao.aero
"""

import platform, logging, os, aiomysql, traceback
from typing import Union
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()
HOST = os.getenv("HOST")
PORT = os.getenv("PORT")
DBUSERNAME = os.getenv("USER")
PASSWORD = os.getenv("PASSWORD")
DATABASE = os.getenv("DATABASE")
PORT = int(PORT)

# Optional logging configuration, you can either set up a custom logger here. In that case further configuration is needed.
# You can also use an already existing logger, just switch the name in getLogger() to your logger's name.
# Further information can be found at https://docs.python.org/3/library/logging.html
logger = logging.getLogger('discord')

if platform.system() == "Darwin":
    dbconfig = {
        "host": "localhost",
        "port": 3306,
        "user": DBUSERNAME,
        "password": PASSWORD,
        "database": DATABASE,
    }
else:
    dbconfig = {
        "host": HOST,
        "port": PORT,
        "user": DBUSERNAME,
        "password": PASSWORD,
        "database": DATABASE,
    }

class MySQLPool:
    """
    Creates a connection pool on startup, which will decrease the time that is spent in
    requesting connection, creating connection, closing connection and executing queries.
    """
    def __init__(self, host: str = HOST, port: int = PORT, user: str = DBUSERNAME, password: str = PASSWORD, database: str = DATABASE) -> None:
        """
        Create a connection pool to mysql

        Parameters
        ----------
        host: `str`
            The host to connect to.
        port: `int`
            The port to connect to.
        user: `str`
            The user to connect with.
        password: `str`
            The password of the user to connect with.
        database: `str`
            The database to connect to.
        
        Returns
        -------
            None if success, else prints the traceback and raises an exception.
        """
        try:
            res = {}
            self._host = host
            self._port = port
            self._user = user
            self._password = password
            self._database = database

            res["host"] = self._host
            res["port"] = self._port
            res["user"] = self._user
            res["password"] = self._password
            res["db"] = self._database
            self.dbconfig = res
            self.pool = None
            return
        except Exception as e:
            traceback.print_exc()
            raise e # Raise the exception, because we don't want to continue if the pool is not created.

    async def create_pool(self) -> aiomysql.pool.Pool:
        """
        Creates a connection pool and returns it as an instance.

        Returns
        ----------
        Connection pool: :class:`aiomysql.pool.Pool`
            An instance of aiomysql.pool.Pool (connection pool to mysql database server)
        Exception: :class:`Exception`
            If there is an error, it will return the exception and print the traceback.
        """

        try:
            self.pool = await aiomysql.create_pool(
                host = HOST,
                port = PORT,
                user = "huivao_discord",
                password = PASSWORD,
                db = DATABASE
            )
            return self.pool
        except Exception as e:
            traceback.print_exc()
            return e

    async def close(self, conn=None, cursor=None) -> Union[bool, Exception]:
        """
        Close the connection and cursor.

        Parameters
        ----------
        conn: :class:`aiomysql.Connection`
            The connection to close, defaults to None
        cursor: :class:`aiomysql.Cursor`
            The cursor to close, defaults to None

        Returns
        -------
            True if close successfully, else return exception
        """
        try:
            cursor.close() if cursor else None
            conn.close() if conn else None
            return True
        except Exception as e:
            return e

    async def execute(self, sql: str, args: tuple = None, commit: bool = False) -> Union[bool, list, Exception]:
        """
        Execute a single SQL statement. Supports both commit and non-commit queries and query with args.
        Args and commit are optional, if not provided, they have a default value of None and False respectively.

        Parameters
        ----------
        sql: `str`
            The SQL query to execute
        args: `tuple`
            The arguments to pass to the query, default None
        commit: `bool`
            Whether to commit the query or not, default False

        Returns
        -------
        List or bool:
            If commit is True, returns True if the query was successfully, else it returns the exception.
            If commit is False, it returns the query result.
        Exception:
            If there is an error, it will return the exception and print the traceback.
        """

        try:
            async with self.pool.acquire() as conn:
                async with conn.cursor() as cursor:
                    await cursor.execute(sql, args) if args else await cursor.execute(sql)

                    if commit:
                        try:
                            await conn.commit()
                            await self.close(conn, cursor)
                            return True
                        except Exception as e:
                            return e
                    else:
                        res = await cursor.fetchall()
                        await self.close(conn, cursor)
                        return res
        except Exception as e:
            traceback.print_exc()
            return e

        finally:
            await self.close(conn, cursor) # Close the connection and cursor even if there is an exception

    async def executemany(self, sql: str, args: tuple = None, commit: bool = False) -> Union[bool, list, Exception]:
        """
        Execute multiple SQL statements. Supports both commit and non-commit queries and query with args.

        Parameters
        ----------
        sql: `str`
            The SQL query to execute
        args: `tuple`
            The arguments to pass to the query
        commit: `bool`
            Whether to commit the query or not, default False

        Returns
        -------
        None or list
            If commit, return None, else return result
        """

        try:
            async with self.pool.acquire() as conn:
                async with conn.cursor() as cursor:
                    await cursor.executemany(sql, args)
                    try:
                        if commit:
                            await conn.commit()
                            await self.close(conn, cursor)
                            return True
                    except Exception as e:
                        return e
                    else:
                        res = await cursor.fetchall()
                        await self.close(conn, cursor)
                        return res
        except Exception as e:
            traceback.print_exc()
            return e

        finally:
            await self.close(conn, cursor) # Close the connection and cursor even if there is an exception


# This is only for testing purposes, you can even remove this part.
# This is only executed if you run this file directly.
# It is advised to import this file as a module and not run it directly.
if  __name__ == "__main__":
    mysql_pool = MySQLPool(**dbconfig)
    logger.info("SQL Pool and connection created.")