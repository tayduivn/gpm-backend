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
        'name'              => 'Granos Uno',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'currency'          => 'USD',
        'quantity'          => 50,
        'user_id'           => '2',
      ],
      [
        'name'              => 'Granos Dos',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 350.25,
        'currency'          => 'USD',
        'quantity'          => 20,
        'user_id'           => '2',
      ],
      [
        'name'              => 'Granos Tres',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 150.25,
        'currency'          => 'USD',
        'quantity'          => 30,
        'user_id'           => '2',
      ],
      [
        'name'              => 'Granos Cuatro',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 550.25,
        'currency'          => 'USD',
        'quantity'          => 40,
        'user_id'           => '2',
      ],
    ];
    $this->table('product')->insert($data)->save();
  }
}
