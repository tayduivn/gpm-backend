<?php

use Phinx\Seed\AbstractSeed;

class InfoPageSeeder extends AbstractSeed {

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
        'title'   => 'Generamos las mejores oportunidades de negocios para nuestros usuarios',
        'content' => 'GPM le permitirá a los usuarios tomar un rol de vendedor o comprador dentro de la aplicación en donde el productor pueda entrar al ecosistema principal de la aplicación',
        'page'    => 'Home',
        'section' => 'header'
      ],
      [
        'title'   => 'GPM “GLOBAL PULSES MARKET”',
        'content' => 'Somos una startup de jóvenes emprendedores, dedicados a optimizar la comercialización electrónica de granos. Nuestro propósito es minimizar las brechas de información y comunicación en toda la cadena comercial sobre precios de oferta y demanda; análisis y tendencias, en un mercado como el de las specialities.',
        'page'    => 'Home',
        'section' => 'about'
      ],
      [
        'title'   => 'Trading',
        'content' => 'Por medio de un sistema de oferta y demanda se podrá consultar el precio de mercado de venta o compra de cada producto. Luego de cada transacción realizada, los usuarios podrán calificar su experiencia, generando un perfil para cada usuario según su comportamiento.',
        'page'    => 'Home',
        'section' => 'trading'
      ],
      [
        'title'   => 'How to make us?',
        'content' => 'A través de una gran red de contactos generados en las principales ferias de alimentos por todo el mundo. Donde nos enfocamos en los clientes que vision a largo plazo, con intensiones de generar fuertes vínculos comerciales.',
        'page'    => 'Home',
        'section' => 'how_to'
      ],
      [
        'title'   => 'Membership GPM',
        'content' => 'Mediante una membresía los usuarios podrán obtener información de alta calidad como: tendencias, novedades, ofertas, entre otros.',
        'page'    => 'Home',
        'section' => 'membership'
      ],
      [
        'title'   => 'Juan Torrealba',
        'content' => 'Excelente plataforma para vender mis productos',
        'page'    => 'Home',
        'section' => 'testimony'
      ],
      [
        'title'   => 'Jesús Pacheco',
        'content' => 'He conseguido lo que necesitaba',
        'page'    => 'Home',
        'section' => 'testimony'
      ],
      [
        'title'   => 'María Gonzalez',
        'content' => 'He realizado mis pagos seguro',
        'page'    => 'Home',
        'section' => 'testimony'
      ],
      [
        'title'   => 'What is Global Pulses Market?',
        'content' => '<p>Somos una startup de jóvenes emprendedores, dedicados a optimizar la comercialización electrónica de granos. Nuestro propósito es minimizar las brechas de información y comunicación en toda la cadena comercial sobre precios de oferta y demanda; análisis y tendencias, en un mercado como el de las specialities.</p><p>GPM tiene como MISIÓN generar las mejores oportunidades de negocios para nuestros usuarios. Esto lo logramos gracias al gran equipo de profesionales, al empoderamiento de la tecnología y a la autonomía de todos los actores participes en la comercialización de los productos.</p> <p>Gracias a una mirada distinta del mercado tradicional, con el compromiso de nuestros usuarios y con una plataforma robusta mejoraremos la capacidad de negocios en plataformas digitales del sector agropecuario.</p>',
        'page'    => 'About',
        'section' => 'what_is'
      ],
      [
        'title'   => 'How do we do it?',
        'content' => 'A través de una gran red de contactos generados en las principales ferias de alimentos por todo el mundo. Donde nos enfocamos en los clientes que vision a largo plazo, con intensiones de generar fuertes vínculos comerciales. Por medio de un sistema de oferta y demanda se podrá consultar el precio de mercado de venta o compra de cada producto. Luego de cada transacción realizada, los usuarios podrán calificar su experiencia, generando un perfil para cada usuario según su comportamiento.',
        'page'    => 'About',
        'section' => 'how_to'
      ],
      [
        'title'   => 'Benefits',
        'content' => '<ul> <li>Incorporar libremente todo tipo de producto de specialities según la oferta o demanda.</li> <li>Gestionar comunicaciones en línea a través de la plataforma.</li> <li>Comercializar productos orgánicos e inorgánicos.</li> <li>Mediante una membresía los usuarios podrán obtener información de alta calidad como: tendencias, novedades, ofertas, entre otros. </li> <li>Contamos con una calculadora de fletes internacionales asociados a empresas marítimas, terrestres y aéreos. </li> </ul>',
        'page'    => 'About',
        'section' => 'benefits'
      ],
      [
        'title'   => 'Pricing monthly',
        'content' => '<ul> <li>Incorporar libremente todo tipo de producto de specialities según la oferta o demanda.</li> <li>Gestionar comunicaciones en línea a través de la plataforma.</li> <li>Comercializar productos orgánicos e inorgánicos.</li> <li>Mediante una membresía los usuarios podrán obtener información de alta calidad como: tendencias, novedades, ofertas, entre otros. </li> <li>Contamos con una calculadora de fletes internacionales asociados a empresas marítimas, terrestres y aéreos. </li> </ul>',
        'page'    => 'Membership',
        'section' => 'info'
      ],
    ];
    $this->table('info_page')->insert($data)->save();
  }
}
