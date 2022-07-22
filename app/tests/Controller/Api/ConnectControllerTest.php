<?php

namespace App\Tests\Controller\Api;

use App\Test\ApiTestTrait;
use App\Test\AutoWhiteListTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserDomainTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConnectControllerTest extends WebTestCase
{
    use ApiTestTrait, DatabaseTestTrait, UserDomainTrait, AutoWhiteListTrait;

}