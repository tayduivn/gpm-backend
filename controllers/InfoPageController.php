<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InfoPageController extends HandleRequest {

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
    $id = $request->getQueryParam('id');

    if ($id !== null) {
      $statement = $this->db->prepare("SELECT * FROM info_page WHERE id = :id");
      $statement->execute(['id' => $id]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM info_page");
      $statement->execute();
    }

    $result = $statement->fetchAll();

    if (is_array($result)) {
      foreach ($result as $index => $infoPage) {
        $result = $this->getInfoImages($this->db, $infoPage, $result, $index);
      }
      return $this->handleRequest($response, 200, '', $result);
    } else {
      return $this->handleRequest($response, 204, '', []);
    }
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $title        = $request_body['title'];
    $content      = $request_body['content'];
    $reference    = $request_body['reference'];

    if (!isset($title) and !isset($content) and !isset($reference)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $prepare = $this->db->prepare("INSERT INTO info_page (`title`, `content`, `reference`) VALUES (:title, :content, :reference)");
    $result  = $prepare->execute(['title' => $title, 'content' => $content, 'reference' => $reference,]);

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $title        = $request_body['title'];
    $content      = $request_body['content'];
    $reference    = $request_body['reference'];

    if (!isset($title) and !isset($content) and !isset($reference)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $query   = "UPDATE info_page SET title = :title, content = :content, reference = :reference WHERE id = :id";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute(['id' => $id, 'title' => $title, 'content' => $content, 'reference' => $reference,]);

    return $this->postSendResponse($response, $result, 'Datos actualizados');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];

    if (!isset($id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $statement = $this->db->prepare("SELECT * FROM info_page WHERE id = :id");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("DELETE FROM info_page WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "Información no encontrada");
    }
  }

}
