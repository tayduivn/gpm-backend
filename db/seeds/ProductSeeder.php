<?php

use Phinx\Seed\AbstractSeed;

class ProductSeeder extends AbstractSeed {

  public function getDependencies() {
    return [
      'UserSeeder',
    ];
  }

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
        'sku'               => '001',
        'name'              => 'Granos Uno',
        'user_id'           => '2',
      ],
      [
        'sku'               => '002',
        'name'              => 'Granos Dos',
        'user_id'           => '2',
      ],
      [
        'sku'               => '003',
        'name'              => 'Granos Tres',
        'user_id'           => '2',
      ],
      [
        'sku'               => '004',
        'name'              => 'Granos Cuatro',
        'user_id'           => '2',
      ],
    ];
    $this->table('product')->insert($data)->save();
  }
}
