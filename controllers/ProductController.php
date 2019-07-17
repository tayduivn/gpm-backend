<?php

namespace App\Controller;

use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController extends HandleRequest {

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
    $order        = $request->getQueryParam('order', $default = 'DESC');
    $limit        = $request->getQueryParam('limit', $default = '12');
    $page         = $request->getQueryParam('page', $page = '1');
    $id           = $request->getQueryParam('id', $default = false);
    $idUser       = $request->getQueryParam('idUser', $default = false);
    $favorite     = $request->getQueryParam('favorite', $default = false);
    $new          = $request->getQueryParam('new', $default = false);
    $shopped      = $request->getQueryParam('shopped', $default = false);
    $category     = $request->getQueryParam('category', $category = false);
    $categoryName = $request->getQueryParam('categoryName');
    $productName  = $request->getQueryParam('productName');

    $skip     = ($page - 1) * $limit;
    $lastPage = 0;
    $count    = 0;

    $all = $new || $favorite || $shopped || $id || $categoryName || $productName || $idUser ? false : true;

    if ($favorite) {
      switch ($order) {
        case 'ASC':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.stars, pr.active, pr.inserted_at, pr.updated_at, pr.user_id, 
                        pr.product_id, pr.message 
                        FROM product INNER JOIN product_sub ps on product.id = ps.product_id 
                        INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' AND pr.active != 0
                        GROUP BY product.id
                        ORDER BY pr.stars ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.stars, pr.active, pr.inserted_at, pr.updated_at, pr.user_id, 
                        pr.product_id, pr.message 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' AND pr.active != 0
                        GROUP BY product.id
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        default:
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.stars, pr.active, pr.inserted_at, pr.updated_at, pr.user_id, 
                        pr.product_id, pr.message 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' AND pr.active != 0
                        GROUP BY product.id
                        ORDER BY pr.stars DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;
      }
    }

    if ($categoryName) {
      var_dump($categoryName);

      $categoryName = explode(",", $categoryName);
      $in           = str_repeat('?,', count($categoryName) - 1) . '?';

      var_dump($categoryName);
      var_dump($in);

      switch ($order) {
        case 'ASC':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pc.id AS product_category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id
                        FROM product INNER JOIN product_category pc on product.id = pc.product_id INNER JOIN category c on pc.category_id = c.id
                        WHERE c.name IN ($in)
                        ORDER BY product.inserted_at ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pc.id AS product_category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id
                        FROM product INNER JOIN product_category pc on product.id = pc.product_id INNER JOIN category c on pc.category_id = c.id
                        WHERE c.name IN ($in)
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          break;

        default:
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pc.id AS product_category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id
                        FROM product INNER JOIN product_category pc on product.id = pc.product_id
                        INNER JOIN category c on pc.category_id = c.id
                        WHERE c.name IN ($in)
                        ORDER BY product.inserted_at DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          break;
      }
      $statement->execute($categoryName);
    }

    if ($new) {
      switch ($order) {
        case 'ASC':
          $query     = "SELECT * FROM product  
                        WHERE product.active != '0'
                        ORDER BY product.inserted_at ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        case 'RAND':
          $query     = "SELECT * FROM product  
                        WHERE product.active != '0'
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        default:
          $query     = "SELECT * FROM product  
                        WHERE product.active != '0'
                        ORDER BY product.inserted_at DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;
      }
    }

    if ($category && $id !== false) {
      $query     = "SELECT c.id AS category_id
                    FROM product INNER JOIN product_category pc on product.id = pc.product_id 
                    INNER JOIN category c on pc.category_id = c.id
                    WHERE product.id = :id AND product.active != '0'";
      $statement = $this->db->prepare($query);
      $statement->execute(['id' => $id]);
      $result = $statement->fetchAll();

      if (is_array($result) && !empty($result)) {
        $myCategories = '';
        foreach ($result as $index => $item) {
          if (!next($result)) {
            $myCategories = $myCategories . $result[$index]['category_id'];
          } else {
            $myCategories = $myCategories . $result[$index]['category_id'] . ',';
          }
        }

        switch ($order) {
          case 'ASC':
            $query     = "SELECT product.id, product.sku, product.name,
                          product.active, product.inserted_at, product.updated_at, product.user_id, 
                          pc.id AS category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id 
                          FROM product INNER JOIN product_category pc on product.id = pc.product_id
                          WHERE product.active != 0 AND pc.active != 0 AND pc.category_id IN ( " . $myCategories . ") 
                          AND product.id != :id GROUP BY product.id
                          ORDER BY product.inserted_at ASC LIMIT " . $limit;
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            break;

          case 'RAND':
            $query     = "SELECT product.id, product.sku, product.name,
                          product.active, product.inserted_at, product.updated_at, product.user_id, 
                          pc.id AS category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id
                          FROM product INNER JOIN product_category pc on product.id = pc.product_id
                          WHERE product.active != 0 AND pc.active != 0 AND pc.category_id IN ( " . $myCategories . ") 
                          AND product.id != :id GROUP BY product.id
                          ORDER BY RAND() LIMIT " . $limit;
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            break;

          default:
            $query     = "SELECT product.id, product.sku, product.name, 
                          product.active, product.inserted_at, product.updated_at, product.user_id, 
                          pc.id AS category_id, pc.active, pc.inserted_at, pc.updated_at, pc.category_id, pc.product_id 
                          FROM product INNER JOIN product_category pc on product.id = pc.product_id
                          WHERE product.active != 0 AND pc.active != 0 AND pc.category_id IN ( " . $myCategories . ") 
                          AND product.id != :id GROUP BY product.id
                          ORDER BY product.inserted_at DESC LIMIT " . $limit;
            $statement = $this->db->prepare($query);
            $statement->execute(['id' => $id]);
            break;
        }
      } else {
        return $this->handleRequest($response, 400, 'Id product incorrect or the product not have categories');
      }
    }

    if ($shopped) {
      switch ($order) {
        case 'ASC':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.message, pr.stars, pr.active, pr.inserted_at, pr.updated_at, 
                        pr.user_id, pr.product_id 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0'
                        ORDER BY product.inserted_at ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.message, pr.stars, pr.active, pr.inserted_at, pr.updated_at, 
                        pr.user_id, pr.product_id 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' 
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        default:
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        pr.id AS review_id, pr.title, pr.message, pr.stars, pr.active, pr.inserted_at, pr.updated_at, 
                        pr.user_id, pr.product_id 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' 
                        ORDER BY pr.stars DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;
      }
    }

    if ($id AND !$category) {
      switch ($order) {
        case 'ASC':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, 
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY product.id ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id,
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;

        default:
          $query     = "SELECT product.id, product.sku, product.name, product.active, 
                        product.inserted_at, product.updated_at, product.user_id
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY product.id DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;
      }
    }

    if ($productName) {
      switch ($order) {
        case 'ASC':
          $query1    = "SELECT count(product.id) FROM product WHERE product.active != '0' AND product.name LIKE '%$productName%'";
          $count     = $this->getCountProducts($query1);
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.name LIKE '%$productName%'
                        ORDER BY product.inserted_at ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        case 'RAND':
          $query1    = "SELECT count(product.id) FROM product WHERE product.active != '0' AND product.name LIKE '%$productName%'";
          $count     = $this->getCountProducts($query1);
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.name LIKE '%$productName%' 
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        default:
          $query1    = "SELECT count(product.id) FROM product WHERE product.active != '0' AND product.name LIKE '%$productName%'";
          $count     = $this->getCountProducts($query1);
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.name LIKE '%$productName%' 
                        ORDER BY product.inserted_at DESC LIMIT " . $skip . "," . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;
      }
    }

    if ($all) {
      switch ($order) {
        case 'ASC':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' ORDER BY product.inserted_at ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        case 'RAND':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        default:
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' ORDER BY product.inserted_at DESC LIMIT " . $skip . "," . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;
      }
    }

    if ($idUser) {
      switch ($order) {
        case 'ASC':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.user_id = :idUser 
                        ORDER BY product.inserted_at LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['idUser' => $idUser]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        case 'RAND':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.user_id = :idUser
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['idUser' => $idUser]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        default:
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.user_id = :idUser
                        ORDER BY product.inserted_at DESC LIMIT " . $skip . "," . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['idUser' => $idUser]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;
      }
    }

    $result = $statement->fetchAll();

    if (is_array($result)) {
      foreach ($result as $index => $product) {
        $result = $this->getSubProducts($this->db, $product, $result, $index);
        $result = $this->getImagesProducts($this->db, $product, $result, $index);
        $result = $this->getCategoriesProducts($this->db, $product, $result, $index);
        $result = $this->getTagsProducts($this->db, $product, $result, $index);
        $result = $this->getReviewsProducts($this->db, $product, $result, $index);
      }
      $pagination = ['count' => (int)$count, 'limit' => (int)$limit, 'lastPage' => $lastPage, 'page' => (int)$page];
      return $this->handleRequest($response, 200, '', $result, $pagination);
    } else {
      return $this->handleRequest($response, 204, '', []);
    }
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $name         = $request_body['name'];
    $user_id      = $request_body['user_id'];

    if (!isset($name) && !isset($user_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $sku = strtoupper(substr(str_replace(' ', '', $name), 0, 10));

    if ($this->existProductName($name)) {
      $query   = "INSERT INTO product (sku, name, user_id) 
        VALUES (:sku, :name, :user_id)";
      $prepare = $this->db->prepare($query);
      $result  = $prepare->execute([
                                     'sku'     => $sku,
                                     'name'    => $name,
                                     'user_id' => $user_id,
                                   ]);
    } else {
      return $this->handleRequest($response, 400, 'Name already exist');
    }

    return $this->postSendResponse($response, $result, 'Data registered');
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $name         = $request_body['name'];
    $user_id      = $request_body['user_id'];

    if (!isset($id) && !isset($name) && !isset($user_id)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->existProductName($name, $id)) {
      $query   = "UPDATE product SET name = :name, user_id = :user_id WHERE id = :id";
      $prepare = $this->db->prepare($query);

      $result = $prepare->execute([
                                    'id'      => $id,
                                    'name'    => $name,
                                    'user_id' => $user_id,
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

    $statement = $this->db->prepare("SELECT * FROM product WHERE id = :id AND active != '0'");
    $statement->execute(['id' => $id]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare("UPDATE product SET active = :active WHERE id = :id");
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
      $statement = $this->db->prepare("SELECT name FROM product WHERE id = :id AND name = :name");
      $statement->execute(['id' => $productId, 'name' => $name]);
      if (!empty($statement->fetchAll())) {
        return true;
      }
    }
    $statement = $this->db->prepare("SELECT name FROM product WHERE name = :name");
    $statement->execute(['name' => $name]);
    return empty($statement->fetchAll());
  }

  /**
   * @param $query
   * @return array
   */
  public function getCountProducts($query) {
    $statement = $this->db->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    $count = $result[0]["count(product.id)"];
    return $count;
  }

}
