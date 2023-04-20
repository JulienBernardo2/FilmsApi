<?php
use Slim\Factory\AppFactory;
use Vanier\Api\Controllers\FilmsController;
use Vanier\Api\Controllers\CustomersController;
use Vanier\Api\Controllers\ActorsController;
use Vanier\Api\Controllers\InfoController;
use Vanier\Api\Controllers\CategoriesController;
use Vanier\Api\Controllers\DistanceController;
use Vanier\Api\Controllers\LanguagesController;
use Vanier\Api\Middleware\ContentNegotiationMiddleware;
use Vanier\Api\Middleware\UnsupportedOperationsMiddleware;

require __DIR__ . '/vendor/autoload.php';   
 // Include the file that contains the application's global configuration settings,
 // database credentials, etc.
require_once __DIR__ . '/src/config/app_config.php';

//--Step 1) Instantiate a Slim app.
$app = AppFactory::create();

//-- Step 2) Add routing middleware.
//$app->add(new ContentNegotiationMiddleware());
$app->addBodyParsingMiddleware();

//-- Step 3) Add error handling middleware.
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->getDefaultErrorHandler()->forceContentType(APP_MEDIA_TYPE_JSON);


//-- Step 4)
// TODO: change the name of the subdirectory here.
// You also need to change it in .htaccess
$app->setBasePath("/films-api");

$app->get('/', [InfoController::class, 'handleGetInfo']);

$app->get('/films', [FilmsController::class, 'handleGetAllFilms']);
$app->get('/films/{film_id}', [FilmsController::class, 'handleGetSingleFilm']);
$app->post('/films', [FilmsController::class, 'handleCreateFilms']);
$app->put('/films', [FilmsController::class, 'handleUpdateFilms']);
$app->delete('/films', [FilmsController::class, 'handleDeleteFilms']);

$app->get('/customers', [CustomersController::class, 'handleGetAllCustomers']);
$app->get('/customers/{customer_id}/films', [CustomersController::class, 'handleGetAllCustomerFilms']);
$app->put('/customers', [CustomersController::class, 'handleUpdateCustomers']);
$app->delete('/customers/{customer_id}', [CustomersController::class, 'handleDeleteCustomer']);

$app->get('/categories', [CategoriesController::class, 'handleGetAllCategory']);
$app->get('/categories/{category_id}/films', [CategoriesController::class, 'handleGetAllCategoryFilms']);

$app->get('/actors', [ActorsController::class, 'handleGetAllActors']);
$app->get('/actors/{actor_id}/films', [ActorsController::class, 'handleGetAllActorFilms']);
$app->post('/actors', [ActorsController::class, 'handleCreateActors']);

$app->post('/distance', [DistanceController::class, 'handleGetDistance']);

$app->get('/languages', [LanguagesController::class, 'handleGetAllLanguages']);

//-- Step 5)
// Here we include the file that contains the application routes. 
require_once __DIR__ . '/src/routes/api_routes.php';

$app->add(new UnsupportedOperationsMiddleware());

// This is a middleware that should be disabled/enabled later. 
//$app->add($beforeMiddleware);
// Run the app.
$app->run();