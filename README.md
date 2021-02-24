# Laravel 5 Starter Project

# Creating a New App/Site

Before doing anything, you must first go into `app.mk` and update the project and app name.
You do not need the production domain at this time.

After editing `app.mk`, create an `env.mk` file and add in at least the LOCAL_DOMAIN variable. Here is where you
will override any variables for your environment.

Next, log into AWS ECR and create a new repository that is named after the composed project name, followed by `/web/`.
For example, if the composed project name was `laravel`, you would name the repository `laravel/web`.
Note that for temporary development you can use NO_PULL=1 or run a local registry with this command and use 'localhost:5000' as the registry URL:

 docker run -d -p 5000:5000 --restart=always --name registry registry:2

Fourth, run `make create-new-app`, which will automatically install laravel, copy the custom files over,
push the initial image to the AWS ECR repository, and start the project up.

Fifth, open the `TrustedProxies` middleware (`src/app/Http/Middleware/TrustProxies.php`), and change `protected $proxies;` to `protected $proxies = '**';`. This will prevent browsers from blocking `bundle.js`.

Lastly, replace/add the javascript asset file in `welcome.blade.php` to the following:

`<script src="{{ asset('assets/bundle.js', true) }}"></script>`

# Compiling Code

In order to compile code, run `yarn watch` after `make up && make web-shell`. You must have run `yarn install` first. This will automatically refresh the browser window for you as well.

# Pushing to Repository

When done with work and ready to push to a repository, commit all changes and push to master. Then, run `make release`.
Once this is done, go to staging/production and run `git pull`, then `make up`.
