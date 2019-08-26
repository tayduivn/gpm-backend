<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GlobalConfigController extends HandleRequest {

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
    $id   = $request->getQueryParam('id');
    $page = $request->getQueryParam('page');

    if ($id !== null) {
      $statement = $this->db->prepare("SELECT * FROM global_config WHERE id = :id AND active != '0'");
      $statement->execute(['id' => $id]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM global_config WHERE active != '0'");
      $statement->execute();
    }

    $result = $statement->fetchAll();

    if (is_array($result)) {
      if ($page !== null) {
        $body = array();
        foreach ($result as $index => $infoPage) {
          $body = $this->getInfoPages($this->db, $infoPage, $body, $index);
        }
        $result = $body;
      } else {
        foreach ($result as $index => $infoPage) {
          $result = $this->getInfoImages($this->db, $infoPage, $result, $index);
        }
      }
      return $this->handleRequest($response, 200, '', $result);
    } else {
      return $this->handleRequest($response, 204, '', []);
    }
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $membership   = $request_body['membership'];
    $percentage   = $request_body['percentage'];

    if (!isset($membership) and !isset($percentage)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $prepare = $this->db->prepare("INSERT INTO global_config (`membership`, `percentage`) VALUES (:membership, :percentage)");
    $result  = $prepare->execute(['membership' => $membership, 'percentage' => $percentage,]);

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $membership   = $request_body['membership'];
    $percentage   = $request_body['percentage'];

    if (!isset($membership) and !isset($percentage)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $query   = "UPDATE global_config SET membership = :membership, percentage = :percentage WHERE id = :id";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute(['id' => $id, 'membership' => $membership, 'percentage' => $percentage,]);

    return $this->postSendResponse($response, $result, 'Datos actualizados');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];

    if (!isset($id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $statement = $this->db->prepare("SELECT * FROM global_config WHERE id = :id");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("UPDATE global_config SET active = :active WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "Información no encontrada");
    }
  }

}
