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
    /*$faker = Faker\Factory::create();
    $data  = [];
    for ($i = 0; $i < 10; $i++) {
      $data[] = [
        'sku'           => $faker->userName,
        'password'      => sha1($faker->password),
        'password_salt' => sha1('foo'),
        'email'         => $faker->email,
        'first_name'    => $faker->firstName,
        'last_name'     => $faker->lastName,
        'created'       => date('Y-m-d H:i:s'),
      ];
    }*/

    $data = [
      [
        'sku'               => '001',
        'name'              => 'Granos Uno',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'nutrition'         => 'https://gpmbackend.herokuapp.com/src/uploads/granos-1.jpg',
        'preparation'       => '1. Ingres el batido',
        'quantity'          => 50,
        'user_id'           => '2',
      ],
      [
        'sku'               => '002',
        'name'              => 'Granos Dos',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'nutrition'         => 'https://gpmbackend.herokuapp.com/src/uploads/granos-2.jpg',
        'preparation'       => '1. Ingres el batido',
        'quantity'          => 50,
        'user_id'           => '2',
      ],
      [
        'sku'               => '003',
        'name'              => 'Granos Tres',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'nutrition'         => 'https://gpmbackend.herokuapp.com/uploads/granos-3.jpg',
        'preparation'       => '1. Ingres el batido',
        'quantity'          => 50,
        'user_id'           => '2',
      ],
      [
        'sku'               => '004',
        'name'              => 'Granos Cuatro',
        'description_short' => 'Producto para el bienestar',
        'description_one'   => 'Producto para el bienestar one',
        'description_two'   => 'Producto para el bienestar two',
        'regular_price'     => 250.25,
        'nutrition'         => 'https://gpmbackend.herokuapp.com/src/uploads/granos-4.jpg',
        'preparation'       => '1. Ingres el batido',
        'quantity'          => 50,
        'user_id'           => '2',
      ],
    ];
    $this->table('product')->insert($data)->save();
  }
}
