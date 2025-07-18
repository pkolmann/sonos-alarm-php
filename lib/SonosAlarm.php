<?php

use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Exceptions\UnknownGroupException;
use duncan3dc\Sonos\Network;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$appdir = dirname(__DIR__);
require_once "$appdir/vendor/autoload.php";

class SonosAlarm {
    private Network $network;
    private Logger $logger;

    public function __construct($DEBUG = false) {
        $collection = new Discovery();
        $this->network = new Network($collection);
        if ($DEBUG) {
            $this->logger = new Logger("sonos");
            $handler = new StreamHandler("php://stdout", Logger::DEBUG);
            $this->logger->pushHandler($handler);
            $this->network->setLogger($this->logger);
        }
    }

    public function getAlarms(): array
    {
        return $this->network->getAlarms();
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

    /**
     * @throws UnknownGroupException
     */
    public function getSpeakers(): array
    {
        $speakers = $this->network->getSpeakers();
        $result = [];
        foreach ($speakers as $speaker) {
            $result[] = [
                "room" => $speaker->getRoom(),
                "group" => $speaker->getGroup(),
                "ip" => $speaker->getIp(),
                "volume" => $speaker->getVolume(),
                "bass" => $speaker->getBass(),
                "treble" => $speaker->getTreble(),
                "loudness" => $speaker->getLoudness(),
                "indicator" => $speaker->getIndicator(),
                "name" => $speaker->getName(),
                "uuid" => $speaker->getUuid(),
                "coordinator" => $speaker->isCoordinator()
            ];
        }
        return $result;
    }

    /**
     * @throws UnknownGroupException
     * @throws Exception
     */
    public function addAlarm(mixed $room, mixed $time, mixed $frequency): array
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker['uuid'] == $room) {

                // Add Alarm
                $xml = <<<XML
                    <Alarms>
                        <Alarm
                            ID="-1"
                            StartTime="$time:00"
                            Duration="00:10:00"
                            Recurrence="WEEKDAYS" Enabled="0"
                            RoomUUID="$room"
                            ProgramURI="x-rincon-buzzer:0"
                            ProgramMetaData="string"
                            PlayMode="NORMAL" Volume="3" IncludeLinkedZones="0"
                        />
                    </Alarms>
                XML;

                $parser = new duncan3dc\DomParser\XmlParser($xml);
                $element = $parser->getTags("Alarm")[0];

                $alarm = new Alarm($element, $this->network);
                try {
                    $alarm->create();
                } catch (Exception $e) {
                    return ["error" => "Failed to create alarm: " . $e->getMessage()];
                }
                return ["success" => "Alarm added"];
            }
        }
        return ["error" => "Room not found"];
    }
}