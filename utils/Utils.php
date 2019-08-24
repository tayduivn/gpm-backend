<?php

namespace App\Utils;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Slim\Http\UploadedFile;

/**
 * Created by PhpStorm.
 * User: Ivans
 * Date: 18/03/2019
 * Time: 9:21
 */
class Utils {

  function moveUploadedFile($directory, UploadedFile $uploadedFile) {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    try {
      $basename   = bin2hex(random_bytes(8));
      $filename   = sprintf('%s.%0.8s', $basename, $extension);
      $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;
      $uploadedFile->moveTo($targetPath);
    } catch (\Exception $e) {
      return "Error";
    }
    return $filename;
  }

  function getBaseURL() {
    $heroku = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https";
    $server = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $args   = ($heroku) || ($server) ? 'https' : 'http';
    return sprintf("%s://%s", $args, $_SERVER['SERVER_NAME']);
  }

  function sendEmail($subject, $message, $emailRecipient, $emailSender = 'desarrollo.theroom@gmail.com') {
    $to      = $emailRecipient;
    $headers = 'From: ' . $emailSender . "\r\n" .
      'Reply-To: ' . $emailSender . "\r\n" .
      'X-Mailer: PHP/' . phpversion();

    mail($to, $subject, $message, $headers);
  }

  /**
   * @param       $db
   * @param       $product
   * @param array $result
   * @param       $index
   * @return array
   */
  function getImagesProducts($db, $product, array $result, $index) {
    $query     = "SELECT product_image.id AS id_image, image, size FROM product_image
                  INNER JOIN product p on product_image.product_id = p.id
                  WHERE product_image.active != 0 AND product_image.product_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $product['id']]);
    $resultImage = $statement->fetchAll();

    if (is_array($resultImage) and !empty($resultImage)) {
      $result[$index]['images'] = $resultImage;
    } else {
      $result[$index]['images'] = [['id_image' => '0', 'image' => $this->getBaseURL() . '/src/uploads/no-image.png']];
    }
    return $result;
  }

  /**
   * @param $db
   * @param $product
   * @param $result
   * @param $index
   * @return mixed
   */
  function getCategoriesProducts($db, $product, $result, $index) {
    $query     = "SELECT category.id, category.name FROM category
                  INNER JOIN product_category pc on category.id = pc.category_id
                  WHERE category.active != 0 AND pc.product_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $product['id']]);
    $resultCategory = $statement->fetchAll();

    if (is_array($resultCategory) and !empty($resultCategory)) {
      $result[$index]['categories'] = $resultCategory;
    }
    return $result;
  }

  /**
   * @param $db
   * @param $product
   * @param $result
   * @param $index
   * @return mixed
   */
  function getTagsProducts($db, $product, $result, $index) {
    $query     = "SELECT tag.id, tag.name FROM tag
                  INNER JOIN product_tag pc on tag.id = pc.tag_id
                  WHERE tag.active != 0 AND pc.product_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $product['id']]);
    $resultCategory = $statement->fetchAll();

    if (is_array($resultCategory) and !empty($resultCategory)) {
      $result[$index]['tags'] = $resultCategory;
    }
    return $result;
  }

  /**
   * @param $db
   * @param $product
   * @param $result
   * @param $index
   * @return mixed
   */
  function getUserProducts($db, $product, $result, $index) {
    $query     = "SELECT u.id, u.email, u.photo, u.first_name, u.last_name, u.address, u.city, 
                u.state, u.country, u.country_code, u.postal_code, u.phone, u.firebase_id, u.inserted_at, u.updated_at 
                FROM product
                INNER JOIN user u on product.user_id = u.id
                WHERE u.active != 0 AND product.id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $product['id']]);
    $resultCategory = $statement->fetchAll();

    if (is_array($resultCategory) and !empty($resultCategory)) {
      $result[$index]['user'] = $resultCategory;
    }
    return $result;
  }

  /**
   * @param $db
   * @param $cart_id
   * @param $result
   * @param $index
   * @return mixed
   */
  function getCartsProducts($db, $cart_id, $result, $index) {
    $query     = "SELECT 
                    cp.id AS cart_product_id, cp.quantity AS cart_quantity, cp.inserted_at, cp.updated_at, cp.cart_id, cp.product_id,
                    p.id, sku, name AS product_name, p.description_short, p.description_one, p.description_two, p.currency,
                    p.regular_price, p.quantity AS product_quantity, p.inserted_at AS product_inserted_ad, 
                    p.updated_at AS product_updated_at, p.user_id
                    FROM cart_products cp INNER JOIN product p on cp.product_id = p.id
                    WHERE p.active != '0' AND cp.cart_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $cart_id]);
    $resultProduct = $statement->fetchAll();

    if (is_array($resultProduct) and !empty($resultProduct)) {
      $result[$index]['products'] = $resultProduct;
    }
    return $result;
  }

  /**
   * @param $db
   * @param $product
   * @param $result
   * @param $index
   * @return mixed
   */
  function getReviewsProducts($db, $product, $result, $index) {
    $query     = "SELECT product_review.id, product_review.title, product_review.stars, product_review.active, 
                  product_review.inserted_at, product_review.updated_at, product_review.user_id, 
                  product_review.product_id, product_review.message
                  FROM product_review
                  INNER JOIN product p on product_review.product_id = p.id
                  WHERE product_review.active != 0 AND p.id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $product['id']]);
    $resultReview = $statement->fetchAll();

    if (is_array($resultReview) and !empty($resultReview)) {
      $result[$index]['reviews'] = $resultReview;
    }
    return $result;
  }

  /**
   * @param $cart_id
   * @param $db
   * @return string
   */
  public function isAlreadyCartOrder($cart_id, $db) {
    $statement = $db->prepare("SELECT * FROM `order` WHERE cart_id = :cartId");
    $statement->execute(['cartId' => $cart_id]);
    return !empty($statement->fetchAll());
  }

  /**
   * @param $db
   * @param $cart_id
   * @param $product_id
   * @return string
   */
  public function isAlreadyProduct($db, $cart_id, $product_id) {
    $query     = "SELECT * FROM cart_products LEFT JOIN cart c on cart_products.cart_id = c.id
                  WHERE cart_id = :cartId AND product_id = :product_id AND status = 'current'";
    $statement = $db->prepare($query);
    $statement->execute(['cartId' => $cart_id, 'product_id' => $product_id]);
    return !empty($statement->fetchAll());
  }

  /**
   * @param $db
   * @param $productID
   * @param $productQuantity
   * @return bool
   */
  public function validateQuantityProduct($db, $productID, $productQuantity) {
    $queryProduct = "SELECT quantity FROM product WHERE product.id = :id";
    $prepare      = $db->prepare($queryProduct);
    $prepare->execute(['id' => $productID]);
    $result = $prepare->fetchObject();
    return is_object($result) AND $productQuantity > (int)$result->quantity;
  }

  /**
   * @param       $db
   * @param       $infoPage
   * @param array $result
   * @param       $index
   * @return array
   */
  function getInfoPages($db, $infoPage, array $result, $index) {
    $query     = "SELECT info_page_image.id AS id_image, image FROM info_page_image 
                    INNER JOIN info_page ip on info_page_image.info_page_id = ip.id
                    WHERE info_page_image.info_page_id = :id";
    $statement = $db->prepare($query);
    $statement->execute(['id' => $infoPage['id']]);
    $resultImage = $statement->fetchAll();

    $query     = "SELECT id, active, title, content, page, section, inserted_at, updated_at FROM info_page WHERE info_page.section = :section";
    $statement = $db->prepare($query);
    $statement->execute(['section' => $infoPage['section']]);
    $resultPage = $statement->fetchAll();

    if (is_array($resultPage)) {
      foreach ($resultPage as $i => $item) {
        $result[$index][$infoPage['section']][$i]['id']          = $item['id'];
        $result[$index][$infoPage['section']][$i]['title']       = $item['title'];
        $result[$index][$infoPage['section']][$i]['content']     = $item['content'];
        $result[$index][$infoPage['section']][$i]['page']        = $item['page'];
        $result[$index][$infoPage['section']][$i]['section']     = $item['section'];
        $result[$index][$infoPage['section']][$i]['inserted_at'] = $item['inserted_at'];
        $result[$index][$infoPage['section']][$i]['updated_at']  = $item['updated_at'];

        if (is_array($resultImage) and !empty($resultImage)) {
          $result[$index][$infoPage['section']][$i]['images'] = $resultImage;
        } else {
          $result[$index][$infoPage['section']][$i]['images'] = [['id_image' => '0', 'image' => $this->getBaseURL() . '/src/uploads/no-image.png']];
        }
      }
    }
    return $result;
  }

  /* PAYMENTS */

  /**
   * Returns PayPal HTTP client instance with environment that has access
   * credentials context. Use this instance to invoke PayPal APIs, provided the
   * credentials have access.
   */
  public function client() {
    return new PayPalHttpClient(self::environment());
  }

  /**
   * Set up and return PayPal PHP SDK environment with PayPal access credentials.
   * This sample uses SandboxEnvironment. In production, use LiveEnvironment.
   */
  public function environment() {
    $clientId     = getenv("CLIENT_ID") ?: "AeXmWVVQuA7uLDl_CYZjP_YMo053Fo5XQDEzCqvd441ipe6aLdb7HpLQ80y6DFL18tkYUMGFIsy5BiUf";
    $clientSecret = getenv("CLIENT_SECRET") ?: "EMM25SR61N74ikDJemLdOxu45tvaNx9danczY4BSDkMDD-w3FRP2BtRCof1EVNLT3I448jbV_uLk3zjJ";
    return new SandboxEnvironment($clientId, $clientSecret);
  }

  /**
   * @param        $db
   * @param        $tokenStripe
   * @param        $total
   * @param        $currency
   * @param string $description
   */
  public function postStripe($db, $tokenStripe, $total, $currency, $description = 'GPM') {
    $statement = $db->prepare("SELECT * FROM payment");
    $statement->execute();
    $result = $statement->fetchAll();

    \Stripe\Stripe::setApiKey($result[0]['stripe_secret_token']);
    $customer = \Stripe\Customer::create([
                                           'email'  => $tokenStripe['email'],
                                           'source' => $tokenStripe['id'],
                                         ]);

    $charge = \Stripe\Charge::create([
                                       'customer'    => $customer->id,
                                       'description' => $description,
                                       'amount'      => str_replace(".", "", $total),
                                       'currency'    => $currency,
                                     ]);
  }

  public function postPaypalOrder($orderId) {

    // 3. Call PayPal to get the transaction details
    $client         = $this->client();
    $responsePaypal = $client->execute(new OrdersGetRequest($orderId));
    /**
     *Enable the following line to print complete response as JSON.
     */
    //print json_encode($responsePaypal->result);
    $statusCode = "Status Code: {$responsePaypal->statusCode}\n";
    $status     = "Status: {$responsePaypal->result->status}\n";
    $orderId    = "Order ID: {$responsePaypal->result->id}\n";
    $intent     = "Intent: {$responsePaypal->result->intent}\n";
    $links      = "Links:\n";
    // 4. Save the transaction in your database. Implement logic to save transaction to your database for future reference.
    $statusCode = "Gross Amount: {$responsePaypal->result->purchase_units[0]->amount->currency_code} {$responsePaypal->result->purchase_units[0]->amount->value}\n";

    // To print the whole response body, uncomment the following line
    // echo json_encode($response->result, JSON_PRETTY_PRINT);
    return $responsePaypal->statusCode;
  }
}
