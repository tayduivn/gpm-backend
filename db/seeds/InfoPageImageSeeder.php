<?php

use Phinx\Seed\AbstractSeed;

class InfoPageImageSeeder extends AbstractSeed {

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
        'image'    => '../../../assets/logo.png',
        'info_page_id' => '1'
      ],
      [
        'image'    => '../../../assets/logo.png',
        'info_page_id' => '2'
      ],
      [
        'image'    => '../../../assets/logo.png',
        'info_page_id' => '3'
      ],
    ];
    $this->table('info_page_image')->insert($data)->save();
  }
}
