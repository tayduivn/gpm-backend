<?php

/**
 * routes to the images
 */
$app->get('/src/uploads/{image}', function ($request, $response, $args) use ($app) {
  $file = __DIR__ . "/uploads/" . $args['image'];
  if (!file_exists($file)) {
    die("file:$file");
  }
  $image = file_get_contents($file);
  if ($image === false) {
    die("error getting image");
  }
  var_dump($file);
  $response->write($image);
  return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
});

/**
 * routes: /public endpoint not require token by the client
 * routes: /endpoints list endpoints
 * routes: /verify connection
 * the rest of endpoints require token
 */
$app->group('/api', function () use ($app) {
  $app->group('/public', function () use ($app) {

    $app->get('/endpoints', function ($request, $response, $args) use ($app) {
      $routes = array_reduce($app->getContainer()->get('router')->getRoutes(), function ($target, $route) {
        $target[$route->getPattern()] = "";
        return $target;
      }, []);
      return $response->withJson([
                                   'message'    => 'Success',
                                   'statusCode' => 200,
                                   'data'       => $routes,
                                   'error'      => false
                                 ], 200);
    });
    $app->get('/base', function ($request, $response, $args) use ($app) {
      $servername = "localhost";
      $username   = "appgpm_ivans";
      $password   = "Y?Up7*?eCAtH";
      $db         = "appgpm_gpm";

      try {
        $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $routes = "Connected successfully";
      } catch (PDOException $e) {
        $routes = "Connection failed: " . $e->getMessage();
      }

      return $response->withJson([
                                   'message'    => 'Success',
                                   'statusCode' => 200,
                                   'data'       => $routes,
                                   'error'      => false
                                 ], 200);
    });

    $app->post('/users/forgot', 'App\Controller\UserController:forgot');
    $app->post('/users/login', 'App\Controller\UserController:login');
    $app->get('/users/email', 'App\Controller\UserController:getEmail');
    $app->post('/users/register', 'App\Controller\UserController:register');
    $app->post('/emails', 'App\Controller\SingUpEmailController:register');

    $app->get('/info/page', 'App\Controller\InfoPageController:getAll');

    $app->get('/global/config', 'App\Controller\GlobalConfigController:getAll');

    $app->get('/products', 'App\Controller\ProductController:getAll');
    $app->get('/products/filter', 'App\Controller\ProductController:getFilter');

    $app->get('/categories', 'App\Controller\CategoryController:getAll');

    $app->get('/tags', 'App\Controller\TagController:getAll');

    $app->get('/images', 'App\Controller\ProductImageController:getAll');

    $app->get('/reviews', 'App\Controller\ReviewController:getAll');

    $app->get('/roles', 'App\Controller\RoleController:getAll');
  });

  $app->get('/emails', 'App\Controller\SingUpEmailController:getAll');

  $app->get('/users', 'App\Controller\UserController:getAll');
  $app->put('/users', 'App\Controller\UserController:update');
  $app->put('/users/password', 'App\Controller\UserController:updatePassword');
  $app->put('/users/bank', 'App\Controller\UserController:updateBank');
  $app->post('/users/photo', 'App\Controller\UserController:updatePhoto');
  $app->delete('/users', 'App\Controller\UserController:delete');

  $app->get('/transactions', 'App\Controller\TransactionController:getAll');
  $app->post('/transactions', 'App\Controller\TransactionController:register');
  $app->delete('/transactions', 'App\Controller\TransactionController:delete');

  $app->get('/payments', 'App\Controller\PaymentController:getAll');
  $app->put('/payments', 'App\Controller\PaymentController:update');

  $app->post('/products', 'App\Controller\ProductController:register');
  $app->post('/products/update', 'App\Controller\ProductController:update');
  $app->delete('/products', 'App\Controller\ProductController:delete');

  $app->post('/categories', 'App\Controller\CategoryController:register');
  $app->put('/categories', 'App\Controller\CategoryController:update');
  $app->delete('/categories', 'App\Controller\CategoryController:delete');

  $app->post('/tags', 'App\Controller\TagController:register');
  $app->put('/tags', 'App\Controller\TagController:update');
  $app->delete('/tags', 'App\Controller\TagController:delete');

  $app->post('/categories/products', 'App\Controller\ProductCategoryController:register');
  $app->put('/categories/products', 'App\Controller\ProductCategoryController:update');
  $app->delete('/categories/products', 'App\Controller\ProductCategoryController:delete');

  $app->post('/tags/products', 'App\Controller\ProductTagController:register');
  $app->put('/tags/products', 'App\Controller\ProductTagController:update');
  $app->delete('/tags/products', 'App\Controller\ProductTagController:delete');

  $app->post('/images/reg', 'App\Controller\ProductImageController:register');
  $app->post('/images/update', 'App\Controller\ProductImageController:update');
  $app->delete('/images', 'App\Controller\ProductImageController:delete');

  $app->post('/info/page', 'App\Controller\InfoPageController:register');
  $app->put('/info/page', 'App\Controller\InfoPageController:update');
  $app->delete('/info/page', 'App\Controller\InfoPageController:delete');

  $app->post('/global/config', 'App\Controller\GlobalConfigController:register');
  $app->put('/global/config', 'App\Controller\GlobalConfigController:update');
  $app->delete('/global/config', 'App\Controller\GlobalConfigController:delete');

  $app->post('/info/images/reg', 'App\Controller\InfoPageImageController:register');
  $app->post('/info/images/update', 'App\Controller\InfoPageImageController:update');
  $app->delete('/info/images', 'App\Controller\InfoPageImageController:delete');

  $app->get('/sub/products', 'App\Controller\ProductSubController:getAll');
  $app->post('/sub/products/reg', 'App\Controller\ProductSubController:register');
  $app->post('/sub/products/update', 'App\Controller\ProductSubController:update');
  $app->delete('/sub/products', 'App\Controller\ProductSubController:delete');

  $app->post('/reviews', 'App\Controller\ReviewController:register');
  $app->put('/reviews', 'App\Controller\ReviewController:update');
  $app->delete('/reviews', 'App\Controller\ReviewController:delete');

  $app->get('/orders', 'App\Controller\OrderController:getAll');
  $app->post('/orders', 'App\Controller\OrderController:register');
  $app->put('/orders', 'App\Controller\OrderController:update');

  $app->get('/carts', 'App\Controller\CartController:getAll');
  $app->post('/carts', 'App\Controller\CartController:register');
  $app->put('/carts', 'App\Controller\CartController:update');
  $app->delete('/carts', 'App\Controller\CartController:delete');

  $app->post('/roles', 'App\Controller\RoleController:register');
  $app->put('/roles', 'App\Controller\RoleController:update');
  $app->delete('/roles', 'App\Controller\RoleController:delete');

  $app->get('/carts/products', 'App\Controller\CartProductsController:getAll');
  $app->post('/carts/products', 'App\Controller\CartProductsController:register');
  $app->put('/carts/products', 'App\Controller\CartProductsController:updateQuantity');
  $app->delete('/carts/products', 'App\Controller\CartProductsController:delete');
});

// fallback for home page
$app->get('/[{name}]', function ($request, $response, $args) {
  // Sample log message
  $this->logger->info("Slim-Skeleton '/' route");

  // Render index view
  return $this->renderer->render($response, 'index.phtml', $args);
});
