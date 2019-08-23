<?php

use Phinx\Seed\AbstractSeed;

class InfoPageSeeder extends AbstractSeed {

  public function getDependencies() {
    return [
      'InfoPageImageSeeder',
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
        'title'     => 'Generamos las mejores oportunidades de negocios para nuestros usuarios',
        'content'   => 'GPM le permitirá a los usuarios tomar un rol de vendedor o comprador dentro de la aplicación en donde el productor pueda entrar al ecosistema principal de la aplicación',
        'reference' => 'Home'
      ],
      [
        'title'     => 'GPM “GLOBAL PULSES MARKET”',
        'content'   => 'Somos una startup de jóvenes emprendedores, dedicados a optimizar la comercialización electrónica de granos. Nuestro propósito es minimizar las brechas de información y comunicación en toda la cadena comercial sobre precios de oferta y demanda; análisis y tendencias, en un mercado como el de las specialities.',
        'reference' => 'Home'
      ],
      [
        'title'     => 'Trading',
        'content'   => 'Por medio de un sistema de oferta y demanda se podrá consultar el precio de mercado de venta o compra de cada producto. Luego de cada transacción realizada, los usuarios podrán calificar su experiencia, generando un perfil para cada usuario según su comportamiento.',
        'reference' => 'Home'
      ],
      [
        'title'     => 'How to make us?',
        'content'   => 'A través de una gran red de contactos generados en las principales ferias de alimentos por todo el mundo. Donde nos enfocamos en los clientes que vision a largo plazo, con intensiones de generar fuertes vínculos comerciales.',
        'reference' => 'Home'
      ],
      [
        'title'     => 'Membership GPM',
        'content'   => 'Mediante una membresía los usuarios podrán obtener información de alta calidad como: tendencias, novedades, ofertas, entre otros.',
        'reference' => 'Home'
      ],
      [
        'title'     => 'Accept Payments',
        'content'   => '',
        'reference' => 'Home'
      ],
      [
        'title'     => 'Juan Torrealba',
        'content'   => 'Excelente plataforma para vender mis productos',
        'reference' => 'Home'
      ],
      [
        'title'     => 'Jesús Pacheco',
        'content'   => 'He conseguido lo que necesitaba',
        'reference' => 'Home'
      ],
      [
        'title'     => 'María Gonzalez',
        'content'   => 'He realizado mis pagos seguro',
        'reference' => 'Home'
      ],
    ];
    $this->table('info_page')->insert($data)->save();
  }
}
