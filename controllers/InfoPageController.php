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
    $id   = $request->getQueryParam('id');
    $page = $request->getQueryParam('page');

    if ($id !== null) {
      $statement = $this->db->prepare("SELECT * FROM info_page WHERE id = :id AND active != '0'");
      $statement->execute(['id' => $id]);
    } else if ($page !== null) {
      $statement = $this->db->prepare("SELECT * FROM info_page WHERE active != '0' AND page = :page GROUP BY section");
      $statement->execute(['page' => $page]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM info_page WHERE active != '0'");
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
    $title        = $request_body['title'];
    $content      = $request_body['content'];
    $page         = $request_body['page'];
    $section      = $request_body['section'];

    if (!isset($title) and !isset($content) and !isset($page) and !isset($section)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $prepare = $this->db->prepare("INSERT INTO info_page (`title`, `content`, `page`, `section`) VALUES (:title, :content, :page, :section)");
    $result  = $prepare->execute(['title' => $title, 'content' => $content, 'page' => $page, 'section' => $section,]);

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $title        = $request_body['title'];
    $content      = $request_body['content'];
    $page         = $request_body['page'];
    $section      = $request_body['section'];

    if (!isset($title) and !isset($content) and !isset($page) and !isset($section)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $query   = "UPDATE info_page SET title = :title, content = :content, page = :page, section = :section WHERE id = :id";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute(['id' => $id, 'title' => $title, 'content' => $content, 'page' => $page, 'section' => $section,]);

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
      $prepare = $this->db->prepare("UPDATE info_page SET active = :active WHERE id = :id");
      $result  = $prepare->execute(['id' => $id, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Datos eliminados');
    } else {
      return $this->handleRequest($response, 404, "Información no encontrada");
    }
  }

}
