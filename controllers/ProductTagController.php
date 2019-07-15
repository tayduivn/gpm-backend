<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductTagController extends HandleRequest {

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

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $product_id   = $request_body['product_id'];
    $tag_id       = $request_body['tag_id'];

    if (!isset($product_id) && !isset($tag_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $query   = "INSERT INTO product_tag (`product_id`, `tag_id`) VALUES (:product_id, :tag_id)";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute(['product_id' => $product_id, 'tag_id' => $tag_id,]);

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $idtag        = $request_body['id'];
    $product_id   = $request_body['product_id'];
    $tag_id       = $request_body['tag_id'];

    if (!isset($idtag) && !isset($product_id) && !isset($tag_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $str     = "UPDATE product_tag SET product_id = :product_id, tag_id = :tag_id WHERE id = :idtag";
    $prepare = $this->db->prepare($str);
    $result  = $prepare->execute(['idtag' => $idtag, 'product_id' => $product_id, 'tag_id' => $tag_id,]);

    return $this->postSendResponse($response, $result, 'Datos actualizados');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $product_id   = $request_body['product_id'];
    $tag_id       = $request_body['tag_id'];

    if (!isset($product_id) && !isset($tag_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    if (!$this->checkProductCategory($product_id, $tag_id)) {
      $query   = "DELETE FROM product_tag WHERE tag_id = :tag_id AND product_id = :product_id";
      $prepare = $this->db->prepare($query);
      $result  = $prepare->execute(['product_id' => $product_id, 'tag_id' => $tag_id,]);

      return $this->postSendResponse($response, $result, 'Data eliminated');
    } else {
      return $this->handleRequest($response, 404, "Category not exist");
    }
  }

  /**
   * @param $product_id
   * @param $tag_id
   * @return mixed
   */
  public function checkProductCategory($product_id, $tag_id) {
    $query     = "SELECT * FROM product_tag WHERE product_id = :product_id AND tag_id = :tag_id AND active != '0'";
    $statement = $this->db->prepare($query);
    $statement->execute(['product_id' => $product_id, 'tag_id' => $tag_id,]);
    return empty($statement->fetchAll());
  }

}
