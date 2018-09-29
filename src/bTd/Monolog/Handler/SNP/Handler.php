<?php
/**
 * Project: SNPHandler
 *
 * @author    Peter Nerád <nerad.peter@gmail.com>
 * @copyright 2018 Peter Nerád
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace bTd\Monolog\Handler\SNP;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use bTd\SNP\Client\Client;
use bTd\SNP\Protocol\Message\Request\NotifyRequest;
use bTd\SNP\Protocol\Message\Request\RegisterRequest;


/**
 * Class Handler
 *
 * @package bTd\Monolog\Handler\SNP
 */
class Handler extends AbstractProcessingHandler
{
    /**
     * @var Client
     */
    private $client;


    /**
     * Handler constructor
     *
     * @param Client $client
     * @param int    $level
     * @param bool   $bubble
     * @param string $appTitle
     * @param string $appID
     * @param string $icon
     *
     * @throws \bTd\SNP\Protocol\Message\Response\ErrorResponseException
     */
    public function __construct(Client $client, $level=Logger::DEBUG, bool $bubble=true, string $appTitle="Monolog Logger", string $appID="monolog", string $icon="stock:platform-beos")
    {
        $this->client = $client;
        $this->client->register(new RegisterRequest($appID, $appTitle, $icon));
        parent::__construct($level, $bubble);

    }//end __construct()


    /**
     * @param array $record
     *
     * @return void
     * @throws \bTd\SNP\Protocol\Message\Response\ErrorResponseException
     */
    protected function write(array $record): void
    {
        $title   = "[ ".$record['datetime']->format("H:i:s")." ] ".$record["channel"]." ".$record['level_name'];
        $message = $record['message'];
        if (count($record['context']) > 0) {
            $message .= "\r\nContext: [".implode(", ", $record['context'])."]";
        }

        if (count($record['extra']) > 0) {
            $message .= "\r\nExtra: [".implode(", ", $record['extra'])."]";
        }

        switch ($record['level']) {
        case Logger::DEBUG:
            $duration = 5;
            $priority = -2;
            $icon     = "stock:system-new";
            break;
        case Logger::INFO:
        case Logger::NOTICE:
            $duration = 10;
            $priority = 0;
            $icon     = "stock:system-info";
            break;
        case Logger::WARNING:
            $duration = 0;
            $priority = 1;
            $icon     = "stock:system-critical";
            break;
        case Logger::ERROR:
            $duration = 0;
            $priority = 2;
            $icon     = "stock:system-denied";
            break;
        case Logger::CRITICAL:
        case Logger::ALERT:
            $duration = 0;
            $priority = 2;
            $icon     = "stock:system-blocked";
            break;
        case Logger::EMERGENCY:
            $duration = 0;
            $priority = 2;
            $icon     = "stock:system-blocked";
            break;
        }//end switch

        $notify = new NotifyRequest($title, $message, $icon, $duration, $priority);
        $this->client->notify($notify);

    }//end write()


}//end class
