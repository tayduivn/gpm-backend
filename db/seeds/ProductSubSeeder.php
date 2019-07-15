<?php

use Phinx\Seed\AbstractSeed;

class ProductSubSeeder extends AbstractSeed {

  public function getDependencies() {
    return [
      'UserSeeder',
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
        'sku'               => '001',
        'name'              => 'Granos Uno',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'quantity'          => 50,
        'user_id'           => '2',
        'product_id'        => '1',
      ],
      [
        'sku'               => '002',
        'name'              => 'Granos Dos',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'quantity'          => 50,
        'user_id'           => '2',
        'product_id'        => '2',
      ],
      [
        'sku'               => '003',
        'name'              => 'Granos Tres',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'quantity'          => 50,
        'user_id'           => '2',
        'product_id'        => '3',
      ],
      [
        'sku'               => '004',
        'name'              => 'Granos Cuatro',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'quantity'          => 50,
        'user_id'           => '2',
        'product_id'        => '4',
      ],
    ];
    $this->table('product_sub')->insert($data)->save();
  }
}
