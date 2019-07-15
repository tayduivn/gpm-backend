<?php

namespace App\Controller;

use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface as ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends HandleRequest {

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

  public function getEmail(Request $request, Response $response, $args) {
    $email = $request->getQueryParam('email', $default = false);
    if ($email) {
      $user = $this->getEmailQuery($email);

      if (is_object($user)) {
        $token = JWT::encode(['id' => $user->id, 'email' => $user->email], $this->settings['jwt']['secret'], "HS256");

        return $this->handleRequest($response, 200, '', ['user' => $user, 'token' => $token]);
      } else {
        return $this->handleRequest($response, 200, '', ['user' => false]);
      }
    } else {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }
  }

  public function getAll(Request $request, Response $response, $args) {
    $order = $request->getQueryParam('order', $default = 'ASC');
    $limit = $request->getQueryParam('limit', $default = '-1');
    $type  = $request->getQueryParam('type', $default = false);
    $id    = $request->getQueryParam('id', $default = false);

    if ($type) {
      $statement = $this->db->prepare("SELECT user.id AS user_id, user.email, user.first_name, user.last_name, user.password, 
                                        user.address, user.phone, user.active, user.role_id, user.photo,
                                        user.inserted_at, user.updated_at, r.id, r.name, r.active, r.inserted_at, r.updated_at
                                        FROM user INNER JOIN role r on user.role_id = r.id 
                                        WHERE user.active != '0' AND r.name = :type");
      $statement->execute(['type' => $type]);
    } else if ($id) {
      $statement = $this->db->prepare("SELECT user.id AS user_id, user.email, user.first_name, user.last_name, user.password, 
                                        user.address, user.phone, user.active, user.role_id, user.photo,
                                        user.inserted_at, user.updated_at, r.id, r.name, r.active, r.inserted_at, r.updated_at
                                        FROM user INNER JOIN role r on user.role_id = r.id 
                                        WHERE user.active != '0' AND user.id = :id");
      $statement->execute(['id' => $id]);
    } else {
      $statement = $this->db->prepare("SELECT user.id AS user_id, user.email, user.first_name, user.last_name, user.password, 
                                        user.address, user.phone, user.active, user.role_id, user.photo,
                                        user.inserted_at, user.updated_at, r.id, r.name, r.active, r.inserted_at, r.updated_at
                                        FROM user INNER JOIN role r on user.role_id = r.id 
                                        WHERE user.active != '0'");
      $statement->execute();
    }

    $result  = $statement->fetchAll();
    $details = is_array($result) ? $result : [];

    return $this->handleRequest($response, 200, '', $details);
  }

  public function login(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $user         = $this->getEmailQuery($request_body['email']);

    if (!$user) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if (!password_verify($request_body['password'], $user->password)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    unset($user->password);

    $token = JWT::encode(['id' => $user->id, 'email' => $user->email], $this->settings['jwt']['secret'], "HS256");

    return $this->handleRequest($response, 200, '', ['user' => $user, 'token' => $token]);
  }

  public function register(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $email        = $request_body['email'];
    $role_id      = $request_body['role_id'];
    $firebase_id  = $request_body['firebase_id'];
    $password     = isset($request_body['password']) ? $request_body['password'] : '';
    $address      = isset($request_body['address']) ? $request_body['address'] : '';
    $phone        = isset($request_body['phone']) ? $request_body['phone'] : '';
    $first_name   = isset($request_body['first_name']) ? $request_body['first_name'] : '';
    $photo        = isset($request_body['photo']) ? $request_body['photo'] : '';
    $last_name    = isset($request_body['last_name']) ? $request_body['last_name'] : '';
    $city         = isset($request_body['city']) ? $request_body['city'] : '';
    $state        = isset($request_body['state']) ? $request_body['state'] : '';
    $country      = isset($request_body['country']) ? $request_body['country'] : '';
    $country_code = isset($request_body['country_code']) ? $request_body['country_code'] : '';
    $postal_code  = isset($request_body['postal_code']) ? $request_body['postal_code'] : '';

    if (!isset($password) && !isset($email) && !isset($role_id) && !isset($firebase_id)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if ($this->validateUser($email)) {
      return $this->handleRequest($response, 409, "Email already exist");
    } else {
      $query   = "INSERT INTO user (email, first_name, last_name, password, address, city, state, country, country_code, postal_code, phone, role_id, firebase_id, photo) 
                  VALUES (:email, :first_name, :last_name, :password, :address, :city, :state, :country, :country_code, :postal_code, :phone, :role_id, :firebase_id, :photo)";
      $prepare = $this->db->prepare($query);

      $result = $prepare->execute([
                                    'email'        => $email,
                                    'password'     => password_hash($password, PASSWORD_BCRYPT),
                                    'first_name'   => $first_name,
                                    'last_name'    => $last_name,
                                    'address'      => $address,
                                    'city'         => $city,
                                    'state'        => $state,
                                    'country'      => $country,
                                    'country_code' => $country_code,
                                    'postal_code'  => $postal_code,
                                    'phone'        => $phone,
                                    'role_id'      => $role_id,
                                    'firebase_id'  => $firebase_id,
                                    'photo'        => $photo,
                                  ]);

      if ($result) {
        $userId  = $this->db->lastInsertId();
        $prepare = $this->db->prepare("INSERT INTO cart (user_id) VALUES (:user_id)");
        $prepare->execute(['user_id' => $userId]);
        if ($result) {
          $user  = $this->getEmailQuery($email);
          $token = JWT::encode(['id' => $user->id, 'email' => $user->email], $this->settings['jwt']['secret'], "HS256");

          return $this->handleRequest($response, 200, '', ['idUser' => $userId, 'user' => $user, 'token' => $token]);
        } else {
          return $this->handleRequest($response, 500);
        }
      } else {
        return $this->handleRequest($response, 500);
      }
    }
  }

  public function forgot(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $email        = $request_body['email'];

    if (!isset($email))
      return $this->handleRequest($response, 400, 'Email incorrect');

    $statement = $this->db->prepare("SELECT * FROM user WHERE email= :email");
    $statement->bindParam("email", $email);
    $statement->execute();
    $user = $statement->fetchObject();

    if (!$user)
      return $this->handleRequest($response, 400, 'This email not exist');

    $this->sendEmail('Recover password', 'Your password is: ', $args['email']);

    return $this->handleRequest($response, 201);
  }

  public function update(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $address      = isset($request_body['address']) ? $request_body['address'] : '';
    $phone        = isset($request_body['phone']) ? $request_body['phone'] : '';
    $role_id      = $request_body['role_id'];
    $first_name   = isset($request_body['first_name']) ? $request_body['first_name'] : '';
    $last_name    = isset($request_body['last_name']) ? $request_body['last_name'] : '';
    $city         = isset($request_body['city']) ? $request_body['city'] : '';
    $state        = isset($request_body['state']) ? $request_body['state'] : '';
    $country      = isset($request_body['country']) ? $request_body['country'] : '';
    $country_code = isset($request_body['country_code']) ? $request_body['country_code'] : '';
    $postal_code  = isset($request_body['postal_code']) ? $request_body['postal_code'] : '';

    if (!isset($email) && !isset($address) && !isset($phone) && !isset($role_id)) {
      return $this->handleRequest($response, 400, 'Invalid request. Required iduser, street, phone, type');
    }

    $query   = "UPDATE user SET  first_name = :first_name, last_name = :last_name, address = :address, 
                phone = :phone, role_id = :role_id, city = :city, state = :state, country = :country, 
                country_code = :country_code, postal_code = :postal_code  
                WHERE id = :id";
    $prepare = $this->db->prepare($query);
    $result  = $prepare->execute([
                                   'id'           => $id,
                                   'first_name'   => $first_name,
                                   'last_name'    => $last_name,
                                   'address'      => $address,
                                   'city'         => $city,
                                   'state'        => $state,
                                   'country'      => $country,
                                   'country_code' => $country_code,
                                   'postal_code'  => $postal_code,
                                   'phone'        => $phone,
                                   'role_id'      => $role_id,
                                 ]);
    return $result ? $this->handleRequest($response, 201, "Data updated") : $this->handleRequest($response, 500);
  }

  public function updatePhoto(Request $request, Response $response, $args) {
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

    $prepare = $this->db->prepare("UPDATE user SET photo = :image WHERE id = :idimage");
    $result  = $prepare->execute([
                                   'idimage' => $idimage,
                                   'image'   => $this->getBaseURL() . "/src/uploads/" . $filename,
                                 ]);

    return $this->postSendResponse($response, $result, 'Update data');
  }


  public function updatePassword(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $id           = $request_body['id'];
    $password     = $request_body['password'];
    $newPassword  = $request_body['newPassword'];
    $email        = $request_body['email'];

    $statement = $this->db->prepare("SELECT * FROM user WHERE email= :email");
    $statement->execute(["email" => $email]);
    $user = $statement->fetchObject();

    if (!$user) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    if (!password_verify($request_body['password'], $user->password)) {
      return $this->handleRequest($response, 400, 'Password incorrect');
    }

    if (!isset($id) && !isset($password)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    $prepare = $this->db->prepare("UPDATE user SET  password = :password WHERE id = :id");
    $result  = $prepare->execute([
                                   'id'       => $id,
                                   'password' => password_hash($newPassword, PASSWORD_BCRYPT),
                                 ]);
    return $result ? $this->handleRequest($response, 201, "Data updated") : $this->handleRequest($response, 500);
  }

  public function delete(Request $request, Response $response, $args) {
    $request_body = $request->getParsedBody();
    $iduser       = $request_body['id'];

    if (!isset($iduser)) {
      return $this->handleRequest($response, 400, 'Data incorrect');
    }

    $statement = $this->db->prepare("SELECT * FROM user WHERE id = :iduser AND active != '0'");
    $statement->execute(['iduser' => $iduser]);
    $result = $statement->fetch();
    if (is_array($result)) {
      $prepare = $this->db->prepare(
        "UPDATE user SET active = :active WHERE id = :iduser"
      );
      $result  = $prepare->execute(['iduser' => $iduser, 'active' => 0]);
      return $this->postSendResponse($response, $result, 'Data deleted');
    } else {
      return $this->handleRequest($response, 404, "Data incorrect");
    }
  }

  private function validateUser($email) {
    $statement = $this->db->prepare("SELECT count(*) FROM user WHERE email = :email");
    $result    = $statement->execute(['email' => $email]);
    return $result ? $statement->fetchColumn() : 0;
  }

  /**
   * @param $email
   * @return mixed
   */
  public function getEmailQuery($email) {
    $statement = $this->db->prepare("SELECT user.id, user.email, user.password, user.first_name, user.last_name, user.city, user.country, user.state,
                                        user.country_code, user.postal_code, user.address, user.phone, user.active, user.role_id, user.state,
                                        user.inserted_at, user.updated_at, user.photo, user.firebase_id,
                                        r.id AS role_id, r.name, r.active, r.inserted_at AS role_inserted, r.updated_at AS role_updated 
                                        FROM user INNER JOIN role r on user.role_id = r.id WHERE email= :email AND user.active != 0");
    $statement->execute(['email' => $email]);
    $user = $statement->fetchObject();
    return $user;
  }
}
