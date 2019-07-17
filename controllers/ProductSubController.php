<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductSubController extends HandleRequest {

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
    $id    = $request->getQueryParam('id');

    if ($id !== null) {
      $statement = $this->db->prepare("SELECT * FROM product_sub WHERE id = :id AND active != '0'");
      $statement->execute(['id' => $id]);
    } else {
      return $this->handleRequest($response, 404, 'Not found');
    }
    return $this->getSendResponse($response, $statement);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body      = $request->getParsedBody();
    $name              = $request_body['name'];
    $description_short = $request_body['description_short'];
    $description_one   = $request_body['description_one'];
    $description_two   = $request_body['description_two'];
    $regular_price     = $request_body['regular_price'];
    $quantity          = (int)$request_body['quantity'];
    $user_id           = $request_body['user_id'];
    $product_id        = $request_body['product_id'];

    if (!isset($name) && !isset($description_short) && !isset($description_one) && !isset($product_id)
      && !isset($description_two) && !isset($regular_price) && !isset($quantity) && !isset($user_id) && !isset($category_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $sku = strtoupper(substr(str_replace(' ', '', $name), 0, 10));

    if ($this->existProductName($name)) {
      $query   = "INSERT INTO product_sub (sku, name, description_short, description_one, description_two, regular_price, quantity, user_id, product_id) 
        VALUES (:sku, :name,  :description_short,  :description_one,  :description_two, :regular_price, :quantity, :user_id, :product_id)";
      $prepare = $this->db->prepare($query);
      $result  = $prepare->execute([
                                     'sku'               => $sku,
                                     'name'              => $name,
                                     'description_short' => $description_short,
                                     'description_one'   => $description_one,
                                     'description_two'   => $description_two,
                                     'regular_price'     => number_format($regular_price, 2),
                                     'quantity'          => $quantity,
                                     'user_id'           => $user_id,
                                     'product_id'        => $product_id,
                                   ]);
    } else {
      return $this->handleRequest($response, 400, 'Name already exist');
    }

    return $this->postSendResponse($response, $result, 'Data registered');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body      = $request->getParsedBody();
    $id                = $request_body['id'];
    $name              = $request_body['name'];
    $description_short = $request_body['description_short'];
    $description_one   = $request_body['description_one'];
    $description_two   = $request_body['description_two'];
    $regular_price     = $request_body['regular_price'];
    $quantity          = (int)$request_body['quantity'];
    $user_id           = $request_body['user_id'];
    $product_id        = $request_body['product_id'];

    if (!isset($name) && !isset($description_short) && !isset($description_one) && !isset($product_id)
      && !isset($description_two) && !isset($regular_price) && !isset($quantity) && !isset($user_id)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->existProductName($name, $id)) {
      $query   = "UPDATE product_sub
                  SET name = :name, description_short = :description_short, description_one = :description_one, 
                  description_two = :description_two, regular_price = :regular_price, 
                  quantity = :quantity, user_id = :user_id, product_id = :product_id
                  WHERE id = :id";
      $prepare = $this->db->prepare($query);

      $result = $prepare->execute([
                                    'id'                => $id,
                                    'name'              => $name,
                                    'description_short' => $description_short,
                                    'description_one'   => $description_one,
                                    'description_two'   => $description_two,
                                    'regular_price'     => number_format($regular_price, 2),
                                    'quantity'          => $quantity,
                                    'user_id'           => $user_id,
                                    'product_id'        => $product_id,
                                  ]);
    } else {
      return $this->handleRequest($response, 400, 'Name already exist');
    }

    if ($result) {
      return $this->handleRequest($response, 204, 'Data updated');
    } else {
      return $this->handleRequest($response, 500);
    }
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];

    if (!isset($id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $statement = $this->db->prepare("SELECT * FROM product_sub WHERE id = :id AND active != '0'");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("UPDATE product_sub SET active = :active WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "id not found");
    }
  }

  /**
   * @param        $name
   * @param string $productId
   * @return mixed
   */
  public function existProductName($name, $productId = '') {
    if ($productId !== '') {
      $statement = $this->db->prepare("SELECT name FROM product_sub WHERE id = :id AND name = :name");
      $statement->execute(['id' => $productId, 'name' => $name]);
      if (!empty($statement->fetchAll())) {
        return true;
      }
    }
    $statement = $this->db->prepare("SELECT name FROM product_sub WHERE name = :name");
    $statement->execute(['name' => $name]);
    return empty($statement->fetchAll());
  }

}
