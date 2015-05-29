<?php

namespace WaToZendApp\Model;

use Lib\ZendeskClient;

Class Zendesk
{
    public function getAll($params)
    {
        $client = new ZendeskClient("ar");
        $client = $client->getClient();
        return $client->tickets()->findAll($params);
    }
}
