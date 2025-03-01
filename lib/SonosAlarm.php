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
     */
    public function addAlarm(mixed $room, mixed $time, mixed $frequency): array
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker['uuid'] == $room) {

                // Add Alarm
                $xml = <<<XML
                    <u:CreateAlarm xmlns:u="urn:schemas-upnp-org:service:AlarmClock:1">
                        <StartLocalTime>$time:00</StartLocalTime>
                        <Duration>00:30:00</Duration>
                        <Recurrence>DAILY</Recurrence>
                        <Enabled>1</Enabled>
                        <RoomUUID>$room</RoomUUID>
                        <ProgramURI>x-rincon-buzzer:0</ProgramURI>
                        <ProgramMetaData>string</ProgramMetaData>
                        <PlayMode>NORMAL</PlayMode>
                        <Volume>5</Volume>
                        <IncludeLinkedZones>1</IncludeLinkedZones>
                    </u:CreateAlarm>
                XML;

                $xml =

                $alarm = new Alarm($xml, $this->network);
                $ret = $speaker->getAlarms()->create($alarm);
                print_r($ret);
                return ["success" => "Alarm added", "alarm" => $ret, "xml" => $xml];
            }
        }
        return ["error" => "Room not found"];
    }
}