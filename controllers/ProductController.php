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

  /**
   * Queries
   * ?categoryName=${name}&tagName=${name}&orderBy={12}&quantity{1-100}&rangeDate{04-29-2019|03-28-2019}&order=RAND&limit=12&page=${1}
   * @param Request  $request
   * @param Response $response
   * @param          $args
   * @return array
   */
  public function getFilter(Request $request, Response $response, $args) {
    $order        = $request->getQueryParam('order', $default = 'DESC');
    $limit        = $request->getQueryParam('limit', $default = '12');
    $page         = $request->getQueryParam('page', $page = '1');
    $categoryName = $request->getQueryParam('categoryName', $default = false);
    $tagName      = $request->getQueryParam('tagName', $default = false);
    $orderBy      = $request->getQueryParam('orderBy', $default = false);
    $quantity     = $request->getQueryParam('quantity', $default = false);
    $rangeDate    = $request->getQueryParam('rangeDate', $default = false);
    $productName  = $request->getQueryParam('productName', $default = false);

    $skip     = ($page - 1) * $limit;
    $lastPage = 0;
    $count    = 0;

    list($categoryName, $tagName, $valuesWhere) = $this->getValues($categoryName, $tagName, $quantity, $rangeDate, $productName);

    $query = sprintf(/** @lang text */
      "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
               product.description_two, product.regular_price, product.quantity, product.active, product.inserted_at, product.updated_at, product.user_id
               FROM product %s %s %s
               %s %s %s %s %s
               GROUP BY product.id %s %s %s",
      $categoryName ? 'INNER JOIN product_category pc on product.id = pc.product_id INNER JOIN category c on pc.category_id = c.id' : null,
      $tagName ? 'INNER JOIN product_tag pt on product.id = pt.product_id INNER JOIN tag t on pt.tag_id = t.id' : null,
      $orderBy === 'evaluation' ? 'INNER JOIN product_review pr on product.id = pr.product_id' : null,
      $valuesWhere[0],
      $valuesWhere[1],
      $valuesWhere[2],
      $valuesWhere[3],
      $valuesWhere[4],
      !$orderBy ? "ORDER BY product.inserted_at " . $order . " LIMIT " . $skip . ", " . $limit : null,
      $orderBy === 'prices' ? "ORDER BY product.regular_price " . $order . " LIMIT " . $skip . ", " . $limit : null,
      $orderBy === 'evaluation' ? "ORDER BY pr.stars " . $order . " LIMIT " . $skip . ", " . $limit : null
    );

    $statement = $this->db->prepare($query);
    $params    = $this->getParams($categoryName, $tagName);
    $statement->execute($params);
    $result = $statement->fetchAll();

    if (is_array($result)) {
      foreach ($result as $index => $product) {
        $result = $this->getImagesProducts($this->db, $product, $result, $index);
        $result = $this->getCategoriesProducts($this->db, $product, $result, $index);
        $result = $this->getTagsProducts($this->db, $product, $result, $index);
        $result = $this->getReviewsProducts($this->db, $product, $result, $index);
        $result = $this->getUserProducts($this->db, $product, $result, $index);
      }
      $pagination = ['count' => (int)$count, 'limit' => (int)$limit, 'lastPage' => $lastPage, 'page' => (int)$page];
      return $this->handleRequest($response, 200, '', $result, $pagination);
    } else {
      return $this->handleRequest($response, 204, '', []);
    }
  }

  /**
   * @param $categoryName
   * @param $tagName
   * @param $quantity
   * @param $rangeDate
   * @param $productName
   * @return array
   */
  public function getValues($categoryName, $tagName, $quantity, $rangeDate, $productName) {
    $inCategory = '';
    $inTag      = '';

    if ($categoryName) {
      $categoryName = explode(",", $categoryName);
      $inCategory   = str_repeat('?,', count($categoryName) - 1) . '?';
    }

    if ($tagName) {
      $tagName = explode(",", $tagName);
      $inTag   = str_repeat('?,', count($tagName) - 1) . '?';
    }

    if ($quantity) {
      $quantity = explode("-", $quantity);
    }

    if ($rangeDate) {
      $rangeDate = explode("|", $rangeDate);
    }

    $valuesWhere = array();

    if ($categoryName) {
      array_push($valuesWhere, "WHERE c.name IN (" . $inCategory . ")");
    } else {
      array_push($valuesWhere, null);
    }

    if ($tagName) {
      if ($valuesWhere[0] === null) {
        array_push($valuesWhere, "WHERE t.name IN (" . $inTag . ")");
      } else {
        array_push($valuesWhere, "AND t.name IN (" . $inTag . ")");
      }
    } else {
      array_push($valuesWhere, null);
    }

    if ($rangeDate) {
      if ($valuesWhere[0] === null and $valuesWhere[1] === null) {
        array_push($valuesWhere, "WHERE product.inserted_at BETWEEN '" . $rangeDate[0] . "' AND '" . $rangeDate[1] . "'");
      } else {
        array_push($valuesWhere, "AND product.inserted_at BETWEEN '" . $rangeDate[0] . "' AND '" . $rangeDate[1] . "'");
      }
    } else {
      array_push($valuesWhere, null);
    }

    if ($quantity) {
      if ($valuesWhere[0] === null and $valuesWhere[1] === null and $valuesWhere[2] === null) {
        array_push($valuesWhere, "WHERE product.regular_price BETWEEN " . $quantity[0] . " AND " . $quantity[1]);
      } else {
        array_push($valuesWhere, "AND product.regular_price BETWEEN " . $quantity[0] . " AND " . $quantity[1]);
      }
    } else {
      array_push($valuesWhere, null);
    }

    if ($productName) {
      if ($valuesWhere[0] === null and $valuesWhere[1] === null and $valuesWhere[2] === null and $valuesWhere[3] === null) {
        array_push($valuesWhere, "WHERE product.name LIKE '%$productName%'");
      } else {
        array_push($valuesWhere, "AND product.name LIKE '%$productName%'");
      }
    } else {
      array_push($valuesWhere, null);
    }
    return array($categoryName, $tagName, $valuesWhere);
  }

  /**
   * @param $categoryName
   * @param $tagName
   * @return array
   */
  public function getParams($categoryName, $tagName) {
    $params = array();

    if ($categoryName) {
      $params = array_merge($params, $categoryName);
    }
    if ($tagName) {
      $params = array_merge($params, $tagName);
    }
    return $params;
  }

  public function getAll(Request $request, Response $response, $args) {
    $order    = $request->getQueryParam('order', $default = 'DESC');
    $limit    = $request->getQueryParam('limit', $default = '12');
    $page     = $request->getQueryParam('page', $page = '1');
    $id       = $request->getQueryParam('id', $default = false);
    $idUser   = $request->getQueryParam('idUser', $default = false);
    $favorite = $request->getQueryParam('favorite', $default = false);
    $new      = $request->getQueryParam('new', $default = false);
    $shopped  = $request->getQueryParam('shopped', $default = false);
    $category = $request->getQueryParam('category', $category = false);

    $skip     = ($page - 1) * $limit;
    $lastPage = 0;
    $count    = 0;

    $all = $new || $favorite || $shopped || $id || $idUser ? false : true;

    if ($favorite) {
      switch ($order) {
        case 'ASC':
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price, 
                        pr.id AS review_id, pr.title, pr.stars, pr.active, pr.inserted_at, pr.updated_at, pr.user_id, 
                        pr.product_id, pr.message 
                        FROM product INNER JOIN product_review pr on product.id = pr.product_id
                        WHERE product.active != '0' AND pr.active != 0
                        GROUP BY product.id
                        ORDER BY pr.stars ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute();
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price, 
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
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price, 
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
            $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                          product.description_two, product.regular_price, product.quantity, 
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
            $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                          product.description_two, product.regular_price, product.quantity, 
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
            $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                          product.description_two, product.regular_price, product.quantity, 
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
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, 
                        product.description_one, product.description_two, 
                        product.regular_price, product.quantity, product.active, 
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
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, 
                        product.description_one, product.description_two,
                        product.regular_price, product.quantity, product.active, 
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
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, 
                        product.description_one, product.description_two, 
                        product.regular_price, product.quantity, product.active, 
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
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY product.id ASC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;

        case 'RAND':
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY RAND() LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;

        default:
          $query     = "SELECT product.id, product.sku, product.name, product.description_short, product.description_one, 
                        product.description_two, product.quantity, product.active, 
                        product.inserted_at, product.updated_at, product.user_id, product.regular_price
                        FROM product
                        WHERE product.id = :id AND product.active != '0' 
                        ORDER BY product.id DESC LIMIT " . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['id' => $id]);
          break;
      }
    }

    if ($all) {
      switch ($order) {
        case 'ASC':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' ORDER BY product.inserted_at ASC LIMIT " . $skip . "," . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['limit' => $limit, 'skip' => $skip]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        case 'RAND':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' ORDER BY RAND() LIMIT " . $skip . "," . $limit;
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
                        ORDER BY product.inserted_at LIMIT " . $skip . "," . $limit;
          $statement = $this->db->prepare($query);
          $statement->execute(['idUser' => $idUser]);
          $lastPage = (ceil($count / $limit) == 0 ? 1 : ceil($count / $limit));
          break;

        case 'RAND':
          $count     = $this->getCountProducts("SELECT count(product.id) FROM product WHERE product.active != '0'");
          $query     = "SELECT * FROM product 
                        WHERE product.active != '0' AND product.user_id = :idUser
                        ORDER BY RAND() LIMIT " . $skip . "," . $limit;
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
        $result = $this->getImagesProducts($this->db, $product, $result, $index);
        $result = $this->getCategoriesProducts($this->db, $product, $result, $index);
        $result = $this->getTagsProducts($this->db, $product, $result, $index);
        $result = $this->getReviewsProducts($this->db, $product, $result, $index);
        $result = $this->getUserProducts($this->db, $product, $result, $index);
      }
      $pagination = ['count' => (int)$count, 'limit' => (int)$limit, 'lastPage' => $lastPage, 'page' => (int)$page];
      return $this->handleRequest($response, 200, '', $result, $pagination);
    } else {
      return $this->handleRequest($response, 204, '', []);
    }
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

    if (!isset($name) && !isset($description_short) && !isset($description_one)
      && !isset($description_two) && !isset($regular_price) && !isset($quantity) && !isset($user_id) && !isset($category_id)) {
      return $this->handleRequest($response, 400, 'Datos incorrectos');
    }

    $sku = strtoupper(substr(str_replace(' ', '', $name), 0, 10));

    if ($this->existProductName($name)) {
      $query   = "INSERT INTO product (sku, name, description_short, description_one, description_two, regular_price, quantity, user_id) 
        VALUES (:sku, :name,  :description_short,  :description_one,  :description_two, :regular_price, :quantity, :user_id)";
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

    if (!isset($name) && !isset($description_short) && !isset($description_one)
      && !isset($description_two) && !isset($regular_price) && !isset($quantity) && !isset($user_id)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }
    if ($this->existProductName($name, $id)) {
      $query   = "UPDATE product 
                  SET name = :name, description_short = :description_short, description_one = :description_one, 
                  description_two = :description_two, regular_price = :regular_price, 
                  quantity = :quantity, user_id = :user_id
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
