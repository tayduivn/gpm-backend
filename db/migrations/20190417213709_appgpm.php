<?php

use Phinx\Migration\AbstractMigration;

class Appgpm extends AbstractMigration {
  /**
   * Change Method.
   *
   * Write your reversible migrations using this method.
   *
   * More information on writing migrations is available here:
   * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
   *
   * The following commands can be used in this method and Phinx will
   * automatically reverse them when rolling back:
   *
   *    createTable
   *    renameTable
   *    addColumn
   *    renameColumn
   *    addIndex
   *    addForeignKey
   *
   * Remember to call "create()" or "update()" and NOT "save()" when working
   * with the Table class.
   */
  public function change() {
    $this->tableRole();
    $this->tableUser();
    $this->tableProduct();
    $this->tableProductImage();
    $this->tableProductReview();
    $this->tableCart();
    $this->tableCartProducts();
    $this->tableTransaction();
    $this->tableOrder();
    $this->tableCategory();
    $this->tableProductCategory();
    $this->tableTag();
    $this->tableProductTag();
    $this->tableSingUpEmail();
    $this->tablePayment();
    $this->infoPage();
    $this->infoPageImage();
  }

  public function tableRole() {
    if ($this->hasTable('role')) {
      $this->table('role')->drop()->save();
    }
    $this->table('role')
      ->addColumn('name', 'string', ['limit' => 255])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addIndex(['name'], ['unique' => true])
      ->save();
  }

  public function tableUser() {
    if ($this->hasTable('user')) {
      $this->table('user')->drop()->save();
    }
    $this->table('user')
      ->addColumn('email', 'string', ['limit' => 255])
      ->addColumn('photo', 'string', ['limit' => 255, 'default' => 'https://image.flaticon.com/icons/png/512/55/55089.png'])/* Image in host */
      ->addColumn('first_name', 'string', ['limit' => 255])
      ->addColumn('last_name', 'string', ['limit' => 255])
      ->addColumn('password', 'string', ['limit' => 255])
      ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('city', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('state', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('country', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('country_code', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('postal_code', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('phone', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('map_lng', 'decimal', ['precision' => 10])
      ->addColumn('map_lat', 'decimal', ['precision' => 10])
      ->addColumn('status', 'string', ['limit' => 255, 'default' => 'in progress'])
      ->addColumn('email_paypal', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('stripe_code', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('role_id', 'integer')
      ->addColumn('firebase_id', 'string', ['limit' => 255])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addIndex(['email'], ['unique' => true])
      ->addForeignKey('role_id', 'role', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableProduct() {
    if ($this->hasTable('product')) {
      $this->table('product')->drop()->save();
    }
    $this->table('product')
      ->addColumn('sku', 'string', ['limit' => 255])
      ->addColumn('name', 'string', ['limit' => 255])
      ->addColumn('description_short', 'string')
      ->addColumn('description_one', 'string', ['limit' => 255])
      ->addColumn('description_two', 'string')
      ->addColumn('regular_price', 'decimal', ['precision' => 10, 'scale' => 2])
      ->addColumn('quantity', 'integer')
      ->addColumn('currency', 'string')
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('user_id', 'integer')
      ->addIndex(['name'], ['unique' => true])
      ->addForeignKey('user_id', 'user', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableProductImage() {
    if ($this->hasTable('product_image')) {
      $this->table('product_image')->drop()->save();
    }
    $this->table('product_image')
      ->addColumn('image', 'string')
      ->addColumn('size', 'string', ['default' => 'Unspecified'])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('product_id', 'integer')
      ->addForeignKey('product_id', 'product', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableProductReview() {
    if ($this->hasTable('product_review')) {
      $this->table('product_review')->drop()->save();
    }
    $this->table('product_review')
      ->addColumn('title', 'string', ['limit' => 50])
      ->addColumn('message', 'string', ['limit' => 255])
      ->addColumn('stars', 'integer', ['limit' => 1])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('user_id', 'integer')
      ->addColumn('product_id', 'integer')
      ->addForeignKey('user_id', 'user', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('product_id', 'product', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableCart() {
    if ($this->hasTable('cart')) {
      $this->table('cart')->drop()->save();
    }
    $this->table('cart')
      ->addColumn('status', 'enum', ['values' => ['current', 'checkout'], 'default' => 'current'])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('user_id', 'integer')
      ->addForeignKey('user_id', 'user', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableCartProducts() {
    if ($this->hasTable('cart_products')) {
      $this->table('cart_products')->drop()->save();
    }
    $this->table('cart_products')
      ->addColumn('quantity', 'integer')
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('cart_id', 'integer')
      ->addColumn('product_id', 'integer')
      ->addForeignKey('cart_id', 'cart', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('product_id', 'product', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableTransaction() {
    if ($this->hasTable('transaction')) {
      $this->table('transaction')->drop()->save();
    }
    $this->table('transaction')
      ->addColumn('processor', 'string', ['limit' => 255])
      ->addColumn('processor_trans_id', 'string', ['limit' => 255])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addIndex(['id'], ['unique' => true])
      ->save();
  }

  /**
   * status: 'Pending', 'Sending', 'Completed', 'Cancelled'
   */
  public function tableOrder() {
    if ($this->hasTable('order')) {
      $this->table('order')->drop()->save();
    }
    $this->table('order')
      ->addColumn('chat_id', 'string', ['limit' => 255])
      ->addColumn('address', 'string', ['limit' => 255])
      ->addColumn('map_lng', 'decimal', ['precision' => 10])
      ->addColumn('map_lat', 'decimal', ['precision' => 10])
      ->addColumn('subtotal', 'decimal', ['precision' => 10, 'scale' => 2])
      ->addColumn('total', 'decimal', ['precision' => 10, 'scale' => 2])
      ->addColumn('status', 'enum', ['values' => ['Pending', 'Sending', 'Completed', 'Cancelled'], 'default' => 'Pending'])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('user_id', 'integer')
      ->addColumn('cart_id', 'integer')
      ->addColumn('transaction_id', 'integer')
      ->addIndex(['id', 'user_id'], ['unique' => true])
      ->addForeignKey('user_id', 'user', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('cart_id', 'cart', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('transaction_id', 'transaction', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableCategory() {
    if ($this->hasTable('category')) {
      $this->table('category')->drop()->save();
    }
    $this->table('category')
      ->addColumn('name', 'string', ['limit' => 255])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->save();
  }

  public function tableProductCategory() {
    if ($this->hasTable('product_category')) {
      $this->table('product_category')->drop()->save();
    }
    $this->table('product_category')
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('category_id', 'integer')
      ->addColumn('product_id', 'integer')
      ->addForeignKey('category_id', 'category', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('product_id', 'product', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableTag() {
    if ($this->hasTable('tag')) {
      $this->table('tag')->drop()->save();
    }
    $this->table('tag')
      ->addColumn('name', 'string', ['limit' => 255])
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->save();
  }

  public function tableProductTag() {
    if ($this->hasTable('product_tag')) {
      $this->table('product_tag')->drop()->save();
    }
    $this->table('product_tag')
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('tag_id', 'integer')
      ->addColumn('product_id', 'integer')
      ->addForeignKey('tag_id', 'tag', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->addForeignKey('product_id', 'product', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }

  public function tableSingUpEmail() {
    if ($this->hasTable('sing_up_email')) {
      $this->table('sing_up_email')->drop()->save();
    }
    $this->table('sing_up_email')
      ->addColumn('email', 'string', ['limit' => 255])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->save();
  }

  public function tablePayment() {
    if ($this->hasTable('payment')) {
      $this->table('payment')->drop()->save();
    }
    $this->table('payment')
      ->addColumn('stripe_secret_token', 'string', ['limit' => 255])
      ->addColumn('stripe_publishable_token', 'string', ['limit' => 255])
      ->addColumn('production_stripe', 'boolean', ['default' => false])
      ->addColumn('paypal_token', 'string', ['limit' => 255])
      ->addColumn('production_paypal', 'boolean', ['default' => false])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->save();
  }

  public function infoPage() {
    if ($this->hasTable('info_page')) {
      $this->table('info_page')->drop()->save();
    }
    $this->table('info_page')
      ->addColumn('active', 'boolean', ['default' => true])
      ->addColumn('title', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('content', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('page', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('section', 'string', ['limit' => 255, 'null' => true])
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->save();
  }

  public function infoPageImage() {
    if ($this->hasTable('info_page_image')) {
      $this->table('info_page_image')->drop()->save();
    }
    $this->table('info_page_image')
      ->addColumn('image', 'string', ['limit' => 255])
      ->addColumn('info_page_id', 'integer')
      ->addColumn('inserted_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
      ->addForeignKey('info_page_id', 'info_page', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
      ->save();
  }
}
