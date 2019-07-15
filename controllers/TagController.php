<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TagController extends HandleRequest {

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
      $statement = $this->db->prepare("SELECT * FROM tag WHERE id = :id AND active != '0'");
      $statement->execute(['id' => $id]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM tag WHERE active != '0'");
      $statement->execute();
    }

    return $this->getSendResponse($response, $statement);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $name         = $request_body['name'];

    if (!isset($name)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->checkCategoryName($name)) {
      $prepare = $this->db->prepare("INSERT INTO tag (`name`) VALUES (:name)");
      $result  = $prepare->execute(['name' => $name,]);
    } else {
      return $this->handleRequest($response, 409, 'Already name tag');
    }

    return $this->postSendResponse($response, $result, 'Data created');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $idtag   = $request_body['id'];
    $name         = $request_body['name'];

    if (!isset($name)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->checkCategoryName($name)) {
      $prepare = $this->db->prepare("UPDATE tag SET name = :name WHERE id = :idtag");
      $result  = $prepare->execute(['idtag' => $idtag, 'name' => $name,]);
    } else {
      return $this->handleRequest($response, 409, 'Already name tag');
    }

    return $this->postSendResponse($response, $result, 'Data updated');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $idtag   = $request_body['id'];

    if (!isset($idtag)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    $statement = $this->db->prepare("SELECT * FROM tag WHERE id = :idtag AND active != '0'");
    $statement->execute(['idtag' => $idtag]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("UPDATE tag SET active = :active WHERE id = :idtag");
      $result  = $prepare->execute(['idtag' => $idtag, 'active' => 0]);

      return $this->postSendResponse($response, $result, 'Data deleted');
    } else {
      return $this->handleRequest($response, 404, "Category not exist");
    }
  }

  /**
   * @param $name
   * @return mixed
   */
  public function checkCategoryName($name) {
    $statement = $this->db->prepare("SELECT name FROM tag WHERE name = :name AND id != 0");
    $statement->execute(['name' => $name]);
    return empty($statement->fetchAll());
  }

}
