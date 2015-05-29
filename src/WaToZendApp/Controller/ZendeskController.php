<?php

namespace WaToZendApp\Controller;

use Lib\Config;
use Lib\Request;
use Lib\ZendeskClient;

class ZendeskController extends BaseController {

    public function generateTicket()
    {
        $client = new ZendeskClient('ng');
        $client = $client->getClient();

        try {
//            $newTicket = $client->ticket(76)->comments()->findAll();
/*

            $newTicket = $client->ticket()->update(
            array(
                'id' => 76,
                'metadata' => array(
                        'read_comments' => array(
                            '1','2','3'
                        )
                ),
                "status" =>   "pending"
            )
            );
  */          //$newTicket = $client->search(array(
              //  'query' => 'status<solved requester:5491161142881@s.whatsapp.net type:ticket'
            //));

            $newTicket = $client->search(array(
                'query' => "[WhatsApp] status<solved type:ticket"
            ));

            $this->render($newTicket);die;

            $ticketsIds = array();
            foreach ($newTicket->results as $ticket) {
                array_push($ticketsIds, $ticket->id);
            }

            foreach ($ticketsIds as $id) {
                $ticket = $client->ticket()->find(array('id'=> $id));
                $commentsRead = $ticket->ticket->custom_fields;
                foreach ($commentsRead as $field) {
                    if ($field->id == Config::get('ng.zendesk.read_comment_field')) {
                        var_dump($field->value);die;
                    }
                }
            }


            $this->render($client->ticket()->find(array('id'=> 76)));
        } catch(\Exception $e) {
            var_dump($client->debug());
            var_dump($e);
        }

    }
}
