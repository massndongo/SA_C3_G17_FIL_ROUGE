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
        $client = static::createClient();
        $client->request("GET",$url,[],[],[
            "AUTHORIZATION" => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1OTg4OTYyNDksImV4cCI6MTU5ODkzMjI0OSwicm9sZXMiOlsiUk9MRV9BRE1JTiJdLCJ1c2VybmFtZSI6IlZpbmNlbnQifQ.zcoki16R8O4lvy4mAcDg9cOzik1ITwpxFdOjbHsTLgLt1G16wEEa0oqps5uqyWUHWN7iB1mpD8foPaI58-oDU9U25-SR-AKbk9l3X42dSr0zZifnHZgvbalsxXrtnMJggRUpF1V8IsSi0kmA7Nh4Pn_VlDgTLcYfhvzt5gXA7lJ-LazAtpgQI11B8Xw5okVB-vBYi5-n6vNKS5ZLNBT5HnOSWakW__gZ7k_j63JpkOpRH8RsRBHG6CUCsJd5FHjmG5u1fy95P4_JwuiZ6_d_5MfepbTLfR-_z9qEFZzf87ueaLvQBlmV84BdbacUs9aHER7jWL3c16L_hNc1rnLQCJkK1Q-7q6umaah1oV2IUORl3NANcq_4WxeMiSuggd3VL-nVuvngEG71R3uan8aywdlb5G-5p78OFkx5mAeatK95YnNQEnge7JpHkP-wn5qcgvyghkwXVnvgZBealshCC433KYhnYsijr1gXSRGfcFs6WbWbd1gXPqnB23Ex-Ar9e-zgetO3Y-zt9rYeghDXerIquJtWBBmvhePsucyauN_Hm8C8cDPRPu92n_Vtw2_G9w28QWo7By9wYqDk03IsulI3LfB-vK5BSwQa2VkOhMOxFmTYnUr3i5EGgvuXfiudzhyIv67fv3Qd3SuMRXDJD3ZyPb0Zgmi-tevX8s8MSwQ"
        ]);
        $this->assertEquals(Response::HTTP_OK,$client->getResponse()->getStatusCode());
    }

    public function provideUrls()
    {
        return [
            ["/api/admin/profils"],
            ["/api/admins/promos"],
            ["/api/formateurs"],
            ["/api/formateurs/briefs"],
            ["/api/admins/competences"],
            ["/api/admins/grpecompetences/competences"],
            ["/api/admins/grpecompetences"]
        ];
    }
}
