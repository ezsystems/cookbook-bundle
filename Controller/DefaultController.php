<?php

namespace EzSystems\CookbookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function helloAction( $name )
    {
        $response = new Response;
        $response->setContent( "Hello $name" );
        return $response;
    }
}
