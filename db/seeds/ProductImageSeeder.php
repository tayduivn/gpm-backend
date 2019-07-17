<?php

use Phinx\Seed\AbstractSeed;

class ProductImageSeeder extends AbstractSeed {
  public function getDependencies() {
    return [
      'ProductSeeder',
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
        'image'      => 'http://backend.appgpm.com/src/uploads/granos-1.jpg',
        'product_id' => 1,
      ],
      [
        'image'      => 'http://backend.appgpm.com/src/uploads/granos-2.jpg',
        'product_id' => 2,
      ],
      [
        'image'      => 'http://backend.appgpm.com/src/uploads/granos-3.jpg',
        'product_id' => 3,
      ],
      [
        'image'      => 'http://backend.appgpm.com/src/uploads/granos-4.jpg',
        'product_id' => 4,
      ],
    ];
    $this->table('product_image')->insert($data)->save();
  }
}
