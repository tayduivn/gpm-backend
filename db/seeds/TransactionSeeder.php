<?php

use Phinx\Seed\AbstractSeed;

class TransactionSeeder extends AbstractSeed {

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
        'processor'          => 'Paypal',
        'processor_trans_id' => '002',
      ],
      [
        'processor'          => 'Stripe',
        'processor_trans_id' => '003',
      ]
    ];
    $this->table('transaction')->insert($data)->save();
  }
}
