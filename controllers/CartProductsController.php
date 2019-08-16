<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CartProductsController extends HandleRequest {

  private $db       = null;
  private $logger   = null;
  private $settings = null;
  private $session  = null;
  private $upload   = null;

  public function __construct(ContainerInterface $container) {
    $this->db       = $container->get('db');
    $this->logger   = $container->get('logger');
    $this->settings = $container->get('settings');
    $this->session  = $container->get('session');
    $this->upload   = $container->get('upload_directory');
  }

  public function getAll(Request $request, Response $response, $args) {
    $id     = $request->getQueryParam('id');
    $order  = $request->getQueryParam('order', $default = 'ASC');
    $cartId = $request->getQueryParam('cartId');

    if ($id !== null) {
      $query     = "SELECT * FROM cart WHERE id = :id AND active != '0' ORDER BY cart.inserted_at ASC";
      $statement = $this->db->prepare($query);
      $statement->execute(['id' => $id]);
    } elseif ($cartId !== null) {
      $query     = "SELECT * FROM cart_products INNER JOIN product p on cart_products.product_id = p.id
                    WHERE cart_id = :cartId";
      $statement = $this->db->prepare($query);
      $statement->execute(['cartId' => $cartId]);
    } else {
      $query     = "SELECT * FROM cart_products INNER JOIN product p on cart_products.product_id = p.id";
      $statement = $this->db->prepare($query);
      $statement->execute(['cartId' => $cartId]);
    }
    return $this->getSendResponse($response, $statement);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $quantity     = (int)$request_body['quantity'];
    $cart_id      = $request_body['cart_id'];
    $product_id   = $request_body['product_id'];

    if (!isset($quantity) && !isset($cart_id) && !isset($product_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    if ($this->isAlreadyProduct($this->db, $cart_id, $product_id)) {
      return $this->handleRequest($response, 409, 'This product already exist');
    } else if ($this->validateQuantityProduct($this->db, $product_id, $quantity)) {
      return $this->handleRequest($response, 409, 'This quantity is mayor in the store');
    } else {
      $query   = "INSERT INTO cart_products (`quantity`, `cart_id`, `product_id`) 
                    VALUES (:quantity, :cart_id, :product_id)";
      $prepare = $this->db->prepare($query);
      $result  = $prepare->execute([
                                     'quantity'   => $quantity,
                                     'cart_id'    => $cart_id,
                                     'product_id' => $product_id,
                                   ]);
    }

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function updateQuantity(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $products     = $request_body['products'];

    $success = true;
    $result  = true;

    if (!isset($products) && is_array($products)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    foreach ($products as $index => $product) {
      if ($this->validateQuantityProduct($this->db, $product['product_id'], $product['quantity'])) {
        $success = false;
        break;
      } else {
        $query   = "UPDATE cart_products SET quantity = :quantity WHERE id = :id";
        $prepare = $this->db->prepare($query);
        $result  = $prepare->execute(['id' => $product['cart_product_id'], 'quantity' => $product['quantity'],]);
      }
    }
    if ($success) {
      return $this->postSendResponse($response, $result, 'Datos actualizados');
    } else {
      return $this->handleRequest($response, 409, 'The quantity is mayor in the store');
    }
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];

    if (!isset($id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $statement = $this->db->prepare("SELECT * FROM cart_products WHERE id = :id");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("DELETE FROM cart_products WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "Información no encontrada");
    }
  }

}
