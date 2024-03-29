<?php

namespace Lib;

use Zendesk\API\Client as ZendeskAPI;

Class ZendeskClient
{
    /** @var \Zendesk\API\Client */
    private $client;

    private $brandId;

    private $country;

    private $currentTicket;

    public function __construct($country)
    {
        $subDomain      = Config::get("$country.zendesk.sub_domain");
        $username       = Config::get("$country.zendesk.username");
        $token          = Config::get("$country.zendesk.token");
        $this->brandId  = Config::get("$country.zendesk.brand_id");
        $this->country  = $country;

        $this->client = new ZendeskAPI($subDomain, $username);
        $this->client->setAuth('token', $token);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function manageSend($mynumber, $from, $id, $type, $time, $name, $body)
    {
        $message    = $body;
        $fromName   = $name;
        $fromNumber = $from;
        $ticket     = $this->hasOneOpen($fromNumber);

        if ($ticket) {
            $result = $this->client->ticket()->update(
                array(
                    'id' => (int) $ticket->id,
                    'comment' => array(
                        'body'   => (string) $message,
                        'author_id' => (int) $ticket->submitter_id
                        ),
                    )
            );
            $action     = 'update';
        } else {
            $result = $this->client->tickets()->create(
                array(
                    'subject' => "[WhatsApp] $message",
                    'comment' => array (
                        'body'      => $message
                    ),
                    'requester' => array(
                        'name'  => $fromName,
                        'email' => $fromNumber
                    ),
                    'brand_id' => $this->brandId,
                    'priority' => 'normal'
                )
            );
            $action     = 'create';
            sleep(45);
        }

        echo $action . " ticket " . $result->ticket->id . "\n";
    }

    private function hasOneOpen($requester)
    {
        $response = $this->client->search(array(
            'query' => "status<solved requester:$requester type:ticket"
        ));

        if ($response->count > 0) {
            return array_shift($response->results);
        } else {
            return false;
        }
    }

    public function manageGet()
    {
        $ticketsIds = $this->getPendingTicketsIds();

        $newComments = array();
        foreach ($ticketsIds as $id) {
            $comments = $this->getPendingComments($id);
            $commentIds = array();
            foreach ($comments as $comment) {
                array_push($newComments, $comment);
                array_push($commentIds, $comment['id']);
            }
            $this->markAsRead($commentIds);
        }

        return $newComments;
    }

    private function getPendingTicketsIds()
    {
        $ticketsOpens = $this->client->search(array(
            'query' => "[WhatsApp] status<solved type:ticket"
        ));

        $ticketsIds = array();

        foreach ($ticketsOpens->results as $ticket) {
            array_push($ticketsIds, $ticket->id);
        }

        return $ticketsIds;
    }

    private function getPendingComments($id)
    {
        $ticket       = $this->client->ticket()->find(array('id'=> $id));
        $ticket       = $ticket->ticket;
        $this->currentTicket = $ticket;
        $user         = $this->client->users()->find(array('id' => $ticket->submitter_id));
        $user         = $user->user;
        $readComments = $this->getReadCommentsIds();
        $comments     = $this->client->ticket($id)->comments()->findAll();
        $result = array();
        foreach ($comments->comments as $comment) {
            if ((!in_array($comment->id, $readComments)) && ($ticket->submitter_id != $comment->author_id) && ($comment->public)) {
                array_push($result, array(
                    'id'        => $comment->id,
                    'phone'     => $user->email,
                    'message'   => $comment->body
                ));
            }
        }

        return $result;
    }

    private function getReadCommentsIds()
    {
        $ticket         = $this->currentTicket;
        $customFields   = $ticket->custom_fields;
        $ids            = array();

        foreach ($customFields as $field) {
            if ($field->id == Config::get("$this->country.zendesk.read_comment_field")) {
                $ids = explode(',',$field->value);
            }
        }

        return $ids;
    }

    private function markAsRead($commentIds)
    {
        $ticket         = $this->currentTicket;
        $customFields   = $ticket->custom_fields;
        $ids            = array();

        foreach ($customFields as $field) {
            if ($field->id == Config::get("$this->country.zendesk.read_comment_field")) {
                if($field->value != null) {
                    $ids = explode(',', $field->value);
                }
            }
        }

        $ids = array_merge($ids, $commentIds);

        if (count($commentIds) > 0) {
            $this->client->ticket()->update(
                array(
                    'id' => $ticket->id,
                    'custom_fields' => array(
                        Config::get("$this->country.zendesk.read_comment_field") => implode(',', $ids)
                    ),
                )
            );
        }
    }
}
