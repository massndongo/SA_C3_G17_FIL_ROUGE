<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApprenantControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
    */
    public function testGetRequests($url)
    {
        $client = $this->createAuthenticatedClient("Vincent","admin");
        $client->request("GET",$url);
        $this->assertEquals(Response::HTTP_OK,$client->getResponse()->getStatusCode());
    }

    public function provideUrls()
    {
        return [
            ["/api/admins/profils"],
            ["/api/admins/promos"],
            ["/api/formateurs"],
            ["/api/formateurs/briefs"],
            ["/api/admins/competences"],
            ["/api/admins/grpecompetences/competences"],
            ["/api/admins/grpecompetences"]
        ];
    }

    private function createAuthenticatedClient($username,$password)
    {
        $client = static::createClient();
        $client->request(
            "POST",
            "/api/login_check",
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            "{
                'username':$username,
                'password':$password
            }"
        );
        $data = json_decode($client->getResponse()->getContent(),true);
        $client->setServerParameter("HTTP_Authorization",sprintf("Bearer %s","eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1OTkxNTAzNDMsImV4cCI6MTU5OTE4NjM0Mywicm9sZXMiOlsiUk9MRV9BRE1JTiJdLCJ1c2VybmFtZSI6IlZpbmNlbnQifQ.CkF2Js3Nj7dcY0rYmXtML0hDYiBIkdnod4S8iPDRQW4vnIczdFw8QIPPU5NXqo0KaxgqVJSmFkRCHEAAreLuHJil55Tsk0i-blPB2IFrmUWxFPxVGjMH4Ky5ermkbhl-6g0zExDJsoRsqUZROxZntMgRAomWEnMVrPdEUD47pRtyhqqb_zf9AANhwnPcpHMYr1icZHYiwCD26hsdU5xNOrgpTeTy3GyXclZiGgSUYTdCHlOPUHSr6eknihj6OdBreblFGDeEwHCGESr54Cn0ybFZjzyxCJQ4GICHvBFjogiEQIw5kjCqiSfN8IG7v2jlt75c6Yp-nCUHTuPa3V7y-Jy2iRSBWVDCDcQfkQdW3qM3KkaKoL8lqE2EnNuLrrx07o426tpFjYpwgS-0Cbt8eFF-2ClV7-aGvlZRTB8d6NbEaz8iJKaiPZToLyFXk0-oqlUvZhncg7kwZM6z6vUfCqVUt7hdkfgRfmoaTZKgFEVrVyTmWDcZyXXhKXgNaHrXdiY2VioGMLD2pSvbDSeuARURywJH4QxSXLgDOk_49WBT0uIuy5KZLUy5xE_UxXoU7dVjgo0Xvdvay5sXEwci--DT-KjczAQ4wACsfoHocEpMkOGuaQteCWZLQc2G0x6HcCJ2frhTSS602Y-9qy-h8SIzkp_FZEHPgLytgIxFL7E"));
        $client->setServerParameter('CONTENT_TYPE', 'application/json');
        return $client;
    }
}
