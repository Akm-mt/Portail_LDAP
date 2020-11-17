<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Adapter\QueryInterface;



class MainController extends AbstractController
{
    /**
     * @Route("/home", name="home_index")
     * @Route("/", name="home")
     */
    public function login(): Response
    {

        return $this->render('main/home.html.twig');
    }

    /**
     * @Route("/manage-users", name="manage_users")
     */
    public function manage_user(Ldap $ldap): Response
    {   

        $ldap = Ldap::create('ext_ldap', [
            'host' => '192.168.42.77',
            'port' => '389',
        ]);
        $search_dn = "cn=admin,dc=akmt,dc=local";
        $search_password = "20Am1299";
        $ldap->bind($search_dn, $search_password);
        $query = $ldap->query('dc=akmt,dc=local', 'objectClass=inetOrgPerson');
        $results = $query->execute()->toArray();
        
        return $this->render('main/manage_users.html.twig',['results' => $results]);
    }

}

