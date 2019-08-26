<?php

use Phinx\Seed\AbstractSeed;

class GlobalConfigSeeder extends AbstractSeed {
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
        'membership' => '10',
        'percentage' => '5',
      ],
    ];
    $this->table('global_config')->insert($data)->save();

  }
}
