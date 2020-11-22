<?php

namespace App\Controller;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Ldap\Adapter\QueryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;



class MainController extends AbstractController

{

    public $array_ldap = array(
        'buildingname' => 'Building',
        'c' => 'Country',
        'cn' => 'Full Name',
        'co' => 'Country',
        'comment' => 'Comment',
        'commonname' => 'Full Name',
        'company' => 'Company',
        'description' => 'Description',
        'distinguishedname' => 'Distinguished Name',
        'dn' => 'Distinguished Name',
        'department' => 'Department',
        'displayname' => 'Full Name',
        'facsimiletelephonenumber' => 'Facsimile',
        'fax' => 'Facsimile',
        'friendlycountryname' => 'Country',
        'givenname' => 'First Name',
        'homephone' => 'Home Telephone',
        'homepostaladdress' => 'Home Address',
        'info' => 'Information',
        'initials' => 'Middle Initial',
        'ipphone' => 'IP Telephone',
        'l' => 'City',
        'mail' => 'Email Address',
        'mailnickname' => 'User ID',
        'rfc822mailbox' => 'Email Address',
        'mobile' => 'Mobile Telephone',
        'mobiletelephonenumber' => 'Mobile Telephone',
        'name' => 'Full Name',
        'othertelephone' => 'Other Telephone',
        'ou' => 'Organizational Unit',
        'pager' => 'Pager',
        'pagertelephonenumber' => 'Pager',
        'physicaldeliveryofficename' => 'Office',
        'postaladdress' => 'Address',
        'postalcode' => 'Zip Code',
        'postofficebox' => 'Post Office Box',
        'samaccountname' => 'User ID',
        'serialnumber' => 'Serial Number',
        'sn' => 'Last Name',
        'surname' => 'Last Name',
        'st' => 'State',
        'stateorprovincename' => 'State',
        'street' => 'Street',
        'streetaddress' => 'Street',
        'telephonenumber' => 'Telephone',
        'title' => 'Title',
        'uid' => 'User ID',
        'url' => 'Other Web Page',
        'userprincipalname' => 'User ID',
        'wwwhomepage' => 'Main Web Page',
        'memberof' => 'Member Of'
    );

    /**
     * @Route("/home", name="home_index")
     * @Route("/", name="home")
     */
    public function login(): Response
    {

        return $this->render('main/home.html.twig');
    }

    /**
     * @Route("/manage-users", name="manage_users",methods={"GET"})
     */
    public function manage_user(Ldap $ldap, Request $request): Response
    {

        $all_entrys = array();
        $attribues_array = array();
        $keys_array = array();

        $ldap = Ldap::create('ext_ldap', ['host' => '192.168.42.77', 'port' => '389']);
        $search_dn = "cn=admin,dc=akmt,dc=local";
        $search_password = "20Am1299";
        $ldap->bind($search_dn, $search_password);

        $attributes = array('filter' => array('uid', 'givenName', 'sn', 'memberof', 'telephonenumber', 'cn'));
        $query = $ldap->query('dc=akmt,dc=local', '(&(objectclass=person))', $attributes);
        $usersGroup = $query->execute()->toArray();

        foreach ($usersGroup as $userGroup) {
            $dn_user = $userGroup->getDn();
            $attribues = $userGroup->getAttributes();

            foreach ($attribues as $keyAttribue => $valueAttribue) {
                if ($valueAttribue) {
                    $array_memberOf = array();
                    if ($keyAttribue == "memberOf") {
                        foreach ($valueAttribue as $value) {
                            array_push($array_memberOf, ldap_explode_dn($value, 1)[0]);
                        }
                        $attribues_array[$keyAttribue] = implode("; ", $array_memberOf);
                    } else {
                        $attribues_array[$keyAttribue] = implode("; ", $valueAttribue);
                    }
                }
            }
            $attribues_array['Edit'] = "#";
            $all_entrys[ldap_explode_dn($dn_user, 1)[0]] = $attribues_array;
        }

        $all_entrys['count'] = count($usersGroup);

        foreach ($attribues as $keyAttribue => $valueAttribue) {
            if (array_key_exists(strtolower($keyAttribue), $this->array_ldap)) {
                array_push($keys_array, $this->array_ldap[strtolower($keyAttribue)]);
            }
        }
        array_push($keys_array, "Edit");

        if ($request->isXmlHttpRequest()) {

            return $this->render('main/_table_manage_user.html.twig', ['all_entrys' => $all_entrys, 'attributs_keys' => $keys_array]);
        }

        return $this->render('main/manage_users.html.twig', ['all_entrys' => $all_entrys, 'attributs_keys' => $keys_array]);
    }

    /**
     * @Route("/manage-users", name="users_delete", methods={"DELETE"})
     */
    public function deleteUsers(Ldap $ldap, Request $request)
    {   
        $users = json_decode($request->getContent(),true);

        if (!$users) {
            $data = [
              'status' => 404,
              'errors' => "User is empty",
            ];
            return $this->response($data, 404);
        }

        $userID = array();

        $ldap = Ldap::create('ext_ldap', ['host' => '192.168.42.77', 'port' => '389']);
        $search_dn = "cn=admin,dc=akmt,dc=local";
        $search_password = "20Am1299";
        $ldap->bind($search_dn, $search_password);

        $entryManager = $ldap->getEntryManager();

        foreach($users as $user){
                
            $query = $ldap->query('dc=akmt,dc=local', '(&(objectclass=person)(uid='.$user['User ID'].'))');
            $result = $query->execute();

            foreach ($result as $entry){
                $entryManager->remove($entry);
            }
        }

        $data = [
            'status' => 200,
            'message' => "User(s) deleted successfully",
        ];

        return $this->response($data);
    }
 

  /**
   * Returns a JSON response
   *
   * @param array $data
   * @param $status
   * @param array $headers
   * @return JsonResponse
   */
  public function response($data, $status = 200, $headers = [])
  {
    return new JsonResponse($data, $status, $headers);
  }
}
