<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrderController extends HandleRequest {

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
    $id       = $request->getQueryParam('id');
    $userId   = $request->getQueryParam('userId');
    $cartId   = $request->getQueryParam('cartId');
    $type     = $request->getQueryParam('type');
    $my_email = $request->getQueryParam('my_email');
    $status   = $request->getQueryParam('status', $default = 'Pending');

    if ($id !== null) {
      $query     = "SELECT `order`.id AS order_id, `order`.chat_id, `order`.subtotal, `order`.total, `order`.status, `order`.active, 
                    `order`.address, `order`.map_lng, `order`.map_lat, 
                    `order`.inserted_at AS order_inserted_at, `order`.updated_at AS order_updated_at, `order`.user_id, `order`.cart_id, 
                    u.id, u.email, u.first_name, u.last_name, u.password, u.address, u.phone, u.active, 
                    u.city, u.country, u.state, u.country_code, u.postal_code, u.state, u.photo,
                    u.role_id, u.inserted_at, u.updated_at 
                    FROM `order` INNER JOIN user u on `order`.user_id = u.id 
                    WHERE `order`.id = :id AND `order`.active != '0' ORDER BY `order`.inserted_at ASC";
      $statement = $this->db->prepare($query);
      $statement->execute(['id' => $id]);
    }

    if ($userId !== null && $cartId === null) {
      $query     = "SELECT * 
                    FROM `order`
                    WHERE `order`.active != '0' AND user_id = :userId AND `order`.status = :status";
      $statement = $this->db->prepare($query);
      $statement->execute(['status' => $status, 'userId' => $userId]);
    }

    if ($userId !== null && $cartId !== null) {
      $query     = "SELECT `order`.id AS order_id, `order`.chat_id, `order`.subtotal, `order`.total, `order`.status AS order_status, `order`.active,
                    `order`.address, `order`.map_lng, `order`.map_lat,  
                    `order`.inserted_at, `order`.updated_at, `order`.user_id, `order`.cart_id, 
                    c.id, c.status, c.active, c.inserted_at, c.updated_at, c.user_id, 
                    u.id, u.email, u.first_name, u.last_name, u.password, u.address, u.phone, u.active, 
                    u.city, u.country, u.state, u.country_code, u.postal_code, u.state, u.photo,
                    u.role_id, u.inserted_at, u.updated_at 
                    FROM `order` INNER JOIN cart c on `order`.cart_id = c.id INNER JOIN user u on `order`.user_id = u.id
                    WHERE `order`.active != '0' AND `order`.status = :status AND `order`.user_id = :userId AND `order`.cart_id = :cartId";
      $statement = $this->db->prepare($query);
      $statement->execute(['status' => $status, 'userId' => $userId, 'cartId' => $cartId]);
      $result = $statement->fetchAll();

      if (is_array($result)) {
        $result = $this->getCartsProducts($this->db, $cartId, $result, 0);
        return $this->handleRequest($response, 200, '', $result);
      } else {
        return $this->handleRequest($response, 204, '', []);
      }
    } else if ($my_email !== null and $type !== null) {
      $query     = "SELECT `order`.id AS order_id, `order`.chat_id, `order`.subtotal, `order`.total, `order`.status, `order`.active, 
                    `order`.address, `order`.map_lng, `order`.map_lat, 
                    `order`.inserted_at AS order_inserted_at, `order`.updated_at AS order_updated_at, `order`.user_id, `order`.cart_id, 
                    u.id AS user_id_buyer, p.user_id AS user_id_seller
                    FROM `order` INNER JOIN user u on `order`.user_id = u.id
                    INNER JOIN cart c on `order`.cart_id = c.id
                    INNER JOIN cart_products cp on c.id = cp.cart_id
                    INNER JOIN product p on cp.product_id = p.id
                    WHERE `order`.active != '0' AND `order`.status = :status";
      $statement = $this->db->prepare($query);
      $statement->execute(['status' => $status]);
      $result = $statement->fetchAll();
      $index  = 0;
      foreach ($result as $order) {
        $result = $this->getUserByProductSeller($this->db, $order, $result, $index);
        if (($type === 'Seller') && $result[$index]['user_seller']->email !== $my_email) {
          array_splice($result, $index, 1);
          continue;
        } else {
          $result = $this->getUserByProductBuyer($this->db, $order, $result, $index);
          if (($type === 'Buyer') && $result[$index]['user_buyer']->email !== $my_email) {
            array_splice($result, $index, 1);
            continue;
          }
        }
        if ($result[$index]['user_seller']->email === $my_email) {
          $result[$index]['type'] = 'Seller';
        } else if ($result[$index]['user_buyer']->email === $my_email) {
          $result[$index]['type'] = 'Buyer';
        }
        $index++;
      }
      return $this->handleRequest($response, 200, '', $result);
    } else if ($status) {
      $query     = "SELECT `order`.id AS order_id, `order`.chat_id, `order`.subtotal, `order`.total, `order`.status, `order`.active, 
                    `order`.address, `order`.map_lng, `order`.map_lat, 
                    `order`.inserted_at AS order_inserted_at, `order`.updated_at AS order_updated_at, `order`.user_id, `order`.cart_id, 
                    u.id, u.email, u.first_name, u.last_name, u.password, u.address, u.phone, u.active, 
                    u.city, u.country, u.state, u.country_code, u.postal_code, u.state, u.photo,
                    u.role_id, u.inserted_at, u.updated_at 
                    FROM `order` INNER JOIN user u on `order`.user_id = u.id
                    WHERE `order`.active != '0' AND `order`.status = :status";
      $statement = $this->db->prepare($query);
      $statement->execute(['status' => $status]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM `order` WHERE `order`.active != '0'");
      $statement->execute();
    }
    return $this->getSendResponse($response, $statement);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body   = $request->getParsedBody();
    $chat_id        = $request_body['chat_id'];
    $subtotal       = $request_body['subtotal'];
    $total          = $request_body['total'];
    $user_id        = $request_body['user_id'];
    $cart_id        = $request_body['cart_id'];
    $address        = $request_body['address'];
    $map_lng        = $request_body['map_lng'];
    $map_lat        = $request_body['map_lat'];
    $transaction_id = $request_body['transaction_id'];

    if (!isset($subtotal) && !isset($total) && !isset($user_id) && !isset($cart_id)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->isAlreadyCartOrder($cart_id, $this->db)) {
      return $this->handleRequest($response, 409, 'Cart is already cart');
    } else {
      $query   = "INSERT INTO `order` (`chat_id`, `address`, `map_lng`, `map_lat`, `subtotal`, `total`, `user_id`, `cart_id`, `transaction_id`) 
                    VALUES(:chat_id, :address, :map_lng, :map_lat, :subtotal, :total, :user_id, :cart_id, :transaction_id)";
      $prepare = $this->db->prepare($query);
      $result  = $prepare->execute([
                                     'address'        => $address,
                                     'map_lng'        => $map_lng,
                                     'map_lat'        => $map_lat,
                                     'chat_id'        => $chat_id,
                                     'subtotal'       => $subtotal,
                                     'total'          => $total,
                                     'user_id'        => $user_id,
                                     'cart_id'        => $cart_id,
                                     'transaction_id' => $transaction_id,
                                   ]);
    }

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $status       = $request_body['status'];
    $address      = $request_body['address'];
    $map_lng      = $request_body['map_lng'];
    $map_lat      = $request_body['map_lat'];

    if (!isset($id) && !isset($status)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $query   = "UPDATE `order` 
                SET `order`.address = :address, `order`.map_lng = :map_lng, `order`.map_lat = :map_lat, `order`.status = :status 
                WHERE id = :id";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute([
                                   'id'      => $id,
                                   'status'  => $status,
                                   'address' => $address,
                                   'map_lng' => $map_lng,
                                   'map_lat' => $map_lat,
                                 ]);

    return $this->postSendResponse($response, $result, 'Datos actualizados');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];

    if (!isset($id)) {
      return $this->handleRequest($response, 400, 'Missing fields id');
    }

    $statement = $this->db->prepare("SELECT * FROM `order` WHERE id = :id AND active != '0'");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("UPDATE `order` SET `order`.status = :active WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "Información no encontrada");
    }
  }

}
