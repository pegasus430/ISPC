<?php

namespace SmartqBundle\Service;

require_once( dirname(__DIR__) . DIRECTORY_SEPARATOR . 'TcpdfService.php' );

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class TcpdfService 
    extends \SmartqStandalone\TcpdfService 
    implements ContainerAwareInterface
{


    public function __construct( $container ) {

        $this->setContainer( $container );

        // note: ContainerAware has no __construct method

        // $config = $container->getParameter('teamnet_fax_configuration');

        // require_once $teamnetIncludeDir . 'Teamnet/Fax/Soap/Client/SendFax.php';

    }

    /**
     * {@inheritdoc}
     */
    public function setContainer( \Symfony\Component\DependencyInjection\ContainerInterface $container = null ) {
        $this->container = $container;
    }


} 