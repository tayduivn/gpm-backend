<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InfoPageImageController extends HandleRequest {

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
      $statement = $this->db->prepare("SELECT * FROM info_page_image WHERE id = :id");
      $statement->execute(['id' => $id]);
    } else {
      $statement = $this->db->prepare("SELECT * FROM info_page_image");
      $statement->execute();
    }
    return $this->getSendResponse($response, $statement);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $info_page_id   = $request_body['info_page_id'];

    $uploadedFiles = $request->getUploadedFiles();

    $uploadedFile = $uploadedFiles['image'];
    if (isset($uploadedFile) && $uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
      $filename = $this->moveUploadedFile($this->upload, $uploadedFile);
    } else {
      return $this->handleRequest($response, 400, 'No upload image');
    }

    if (!isset($info_page_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $prepare = $this->db->prepare("INSERT INTO info_page_image (`image`, `info_page_id`) VALUES (:image, :info_page_id)");
    $result  = $prepare->execute([
                                   'info_page_id' => $info_page_id,
                                   'image'      => $this->getBaseURL() . "/src/uploads/" . $filename
                                 ]);

    return $this->postSendResponse($response, $result, 'Datos registrados');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $idimage      = $request_body['id'];

    $uploadedFiles = $request->getUploadedFiles();

    $uploadedFile = $uploadedFiles['image'];
    if (isset($uploadedFile) && $uploadedFile !== null && $uploadedFile->getError() === UPLOAD_ERR_OK) {
      $filename = $this->moveUploadedFile($this->upload, $uploadedFile);
    } else {
      return $this->handleRequest($response, 400, 'No upload image');
    }

    if (!isset($idimage)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $prepare = $this->db->prepare("UPDATE info_page_image SET image = :image WHERE id = :idimage");
    $result  = $prepare->execute([
                                   'idimage' => $idimage,
                                   'image'   => $this->getBaseURL() . "/src/uploads/" . $filename,
                                 ]);

    return $this->postSendResponse($response, $result, 'Datos actualizados');
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $idimages     = $request_body['id'];

    if (!isset($idimages)) {
      return $this->handleRequest($response, 400, 'Missing fields idimages');
    }

    $statement = $this->db->prepare("SELECT * FROM info_page_image WHERE id = :idimages");
    $statement->execute(['idimages' => $idimages]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("DELETE FROM info_page_image WHERE id = :idimages");
      $result  = $prepare->execute(['idimages' => $idimages, 'active' => 0]);

      return $this->postSendResponse($response, $result, 'Datos Eliminados');
    } else {
      return $this->handleRequest($response, 404, "id not found");
    }
  }

}
