<?php

namespace Harbour\UnsubscribeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Coral\CoreBundle\Utility\JsonParser;
use Coral\CoreBundle\Test\JsonTestCase;

class DefaultControllerTest extends JsonTestCase
{
    public function testStatus()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doGetRequest('/v1/unsubscriber/status');

        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());

        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));
        $this->assertCount(0, $jsonRequest->getMandatoryParam('items'));
    }

    public function testGenerateHash()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/unsubscriber/generate-hash',
            '{ "group": "ormd2", "recipients": [ "email@example.com", "anotheremail@example.com" ] }'
        );
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $this->assertCount(2, $jsonRequest->getMandatoryParam('items'));
        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertTrue(isset($items['email@example.com']));
        $hash1 = $items['email@example.com'];
        $this->assertTrue(isset($items['anotheremail@example.com']));
        $hash2 = $items['anotheremail@example.com'];
        $this->assertFalse($hash1 == $hash2);

        $client = $this->doPostRequest(
            '/v1/unsubscriber/generate-hash',
            '{ "group": "ormd2", "recipients": [ "email@example.com", "new@example.com" ] }'
        );
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $this->assertCount(2, $jsonRequest->getMandatoryParam('items'));
        $items = $jsonRequest->getMandatoryParam('items');
        $this->assertEquals($hash1, $items['email@example.com']);
        $this->assertFalse($hash2 == $items['new@example.com']);
    }

    public function testImportAndList()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/unsubscriber/import',
            '{ "email@example.com": "ormd2", "new@example.com": "ormd2", "anotheremail@example.com": "group2" }'
        );
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $client = $this->doGetRequest('/v1/unsubscriber/list/ormd2');
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $this->assertCount(2, $jsonRequest->getMandatoryParam('items'));
        $this->assertEquals("email@example.com", $jsonRequest->getMandatoryParam('items[0]'));
        $this->assertEquals("new@example.com", $jsonRequest->getMandatoryParam('items[1]'));

        //try how it handles duplicit records
        $client = $this->doPostRequest(
            '/v1/unsubscriber/import',
            '{ "email@example.com": "ormd2", "new@example.com": "ormd2", "anotheremail@example.com": "group2" }'
        );
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);
    }

    public function testUnsubscribeAndList()
    {
        $this->loadFixtures(array(
            'Coral\CoreBundle\Tests\DataFixtures\ORM\MinimalSettingsData'
        ));

        $client = $this->doPostRequest(
            '/v1/unsubscriber/generate-hash',
            '{ "group": "ormd2", "recipients": [ "email@example.com", "anotheremail@example.com" ] }'
        );
        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $items = $jsonRequest->getMandatoryParam('items');
        $hash1 = $items['email@example.com'];

        $client = $this->doGetRequest('/v1/unsubscriber/unsubscribe/' . $hash1 . '/128');
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $client = $this->doGetRequest('/v1/unsubscriber/list/ormd2');
        $this->assertIsJsonResponse($client);
        $this->assertIsStatusCode($client, 200);

        $jsonRequest  = new JsonParser($client->getResponse()->getContent());
        $this->assertEquals('ok', $jsonRequest->getMandatoryParam('status'));

        $this->assertCount(1, $jsonRequest->getMandatoryParam('items'));
        $this->assertEquals("email@example.com", $jsonRequest->getMandatoryParam('items[0]'));
    }
}
