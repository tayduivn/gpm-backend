<?php

use Phinx\Seed\AbstractSeed;

class PaymentsSeeder extends AbstractSeed {

  /**
   * Run Method.
   *
   * Write your database seeder using this method.
   *
   * More information on writing seeders is available here:
   * http://docs.phinx.org/en/latest/seeding.html
   */
  public function run() {
    $data = [
      [
        'stripe_secret_token'      => 'sk_test_3QkM6e5ir3yPrJUtRaZqXYlE00lQEOAr4I',
        'stripe_publishable_token' => 'pk_test_YeVKMYilUfYqLjz0T8aVkUZG00vF7lQyNZ',
        'paypal_token'             => 'your paypal token',
      ]
    ];
    $this->table('payment')->insert($data)->save();
  }
}
