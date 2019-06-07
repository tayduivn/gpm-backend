<?php

use Phinx\Seed\AbstractSeed;

class RoleSeeder extends AbstractSeed {

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
        'name'    => 'Admin',
      ],
      [
        'name'    => 'Client',
      ],
    ];
    $this->table('role')->insert($data)->save();
  }
}
