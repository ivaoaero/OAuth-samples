# Laravel Pure Code Sample
## Running the example locally 
### 1. Install the dependencies
Run `composer install` in this folder to download all Laravel dependencies.

### 2. Initiate the database
By default, we have configured a local sqlite database inside the `database` folder. 

If you want to connect to another database, please edit the `.env` file and refer to the laravel documention for other configuration options.

Initiate the database structure with this command : `php artisan migrate`

### 3. Start the local server
Run `php artisan serve` to start a local server on [localhost:8000](http://localhost:8000).

### 4. Enjoy
The default configuration will log you in and display all your informations

## How does it work

### 1. Register the routes 
We need to tell Laravel about our 2 new routes, the one that will redirect the user to SSO and the one the user will be redirected to after a successful login.

They are located in the `routes/web.php` file

### 2. Add a controller

Now that the routes exist, we need to tell laravel what do to when a user is accessing them. 

We have copied the example from `php-pure` into `app/Http/Controllers/IvaoController.php`

### 3. Specify the variables
Inside `.env` we have specified the `IVAO_CLIENT_ID` and `IVAO_CLIENT_SECRET` values that will authenticate our application.

## Known bugs

### 127.0.0.1 Invalid Redirect URI
If you open the URL on `http://127.0.0.1:8000` it won't work because the redirect url doesn't allow an IP. You have to open it from [http://localhost:8000](http://localhost:8000) to make it work.

## Contribution 

Thank you for your contribution to this code sample and testing : 
 - [Edgardo Alvarez (602243) CO-WM](https://github.com/edgardoalvarez100)
 - [DZ-WM (670202)](https://github.com/belmeg)