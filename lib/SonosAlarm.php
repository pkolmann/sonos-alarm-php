<?php

use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Network;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";

class SonosAlarm {
    private Network $sonos;
    private Logger $logger;

    public function __construct($DEBUG = false) {
        $collection = new Discovery();
        $this->sonos = new Network($collection);
        if ($DEBUG) {
            $this->logger = new Logger("sonos");
            $handler = new StreamHandler("php://stdout", Logger::DEBUG);
            $this->logger->pushHandler($handler);
            $this->sonos->setLogger($this->logger);
        }
    }

    public function getAlarms(): array
    {
        return $this->sonos->getAlarms();
    }

    /**
     * @throws Exception
     */
    public function getMusicTitle($music): string
    {
        $metaData = $music->getMetaData();

        // Access Data as XML
        $data = new SimpleXMLElement($metaData);

        // Get Namespaces
        $ns = $data->getNamespaces(true);

        // Get Item
        $item = $data->item;

        // Get all chirldren with namespace 'dc'
        $dcs = $item->children($ns['dc']);

        // Return Title
        return $dcs->title;
    }

    public function toggleAlarm(mixed $alarmId): void
    {
        $alarms = $this->getAlarms();
        foreach ($alarms as $alarm) {
            if ($alarm->getId() == $alarmId) {
                $state = $alarm->isActive();
                if ($state) {
                    $alarm->deactivate();
                    print "disabled";
                } else {
                    $alarm->activate();
                    print "active";
                }
                return;
            }
        }
    }
}