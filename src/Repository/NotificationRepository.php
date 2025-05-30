<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;


class NotificationRepository extends BaseRepository
{
    // 
    //     POST https://fcm.googleapis.com/v1/projects/falconpaper-c7f81/messages:send HTTP/1.1

    // Content-Type: application/json
    // Authorization: Bearer ya29.ElqKBGN2Ri_Uz...HnS_uNreA

    // {
    //    "message":{
    //       "token":"bk3RNwTe3H0:CI2k_HHwgIpoDKCIZvvDMExUdFQ3P1...",
    //       "notification":{
    //         "body":"This is an FCM notification message!",
    //         "title":"FCM Message"
    //       }
    //    }
    // }
    public function isNotificationDisabled()
    {
        $sql = " SELECT DISABLE_NOTIFICATIONS FROM " . SETTING;
        $result = $this->getFetshTableWithQuery($sql);
        $result = Helpers::getKeyValueFromObj($result, "DISABLE_NOTIFICATIONS");
        return $result === 1 || $result == 1;
    }

    /**
     * Registers a new notifcication to send 
     *
     * By default it send text to all devices that registered in the server
     * if tablename then send it to the table ex.. customers
     * if tablename not null 
     */
    public function doNotifcationGeneral($notificationObject, ?string $tableName = null, ?int $iD = null, ?string $topic = null, bool $checkForNotificationService = false)
    {
        if ($checkForNotificationService) {
            if ($this->isNotificationDisabled()) {
                throw new \Exception('notification service is disable contact admin to enable it ');
            }
        }
        $isGeneralToAll =  !$tableName && !$iD;
        $option = Options::getInstance();
        $option = $option->withStaticSelect(["token", "name"])->addStaticQuery("(token is not null or token <> '' )")->addStaticQuery(ACTIVATION_FIELD . "=1");
        echo "notification is send to all ?  $isGeneralToAll ";
        $response = array();
        $results = array();
        if ($isGeneralToAll) {
            $results = $this->list(CUST, null, $option);
            $results = array_merge($results, $this->list(EMP, null, $option));
        }
        $isGeneralToTableName = $tableName && !$iD;
        echo "notification is send to tableName ?  $isGeneralToTableName ";

        if ($isGeneralToTableName) {
            $results = $this->list($tableName, null, $option);
        }
        $isToSpecificUser = $tableName && $iD;
        echo "notification is send to user name only ?  $isToSpecificUser ";
        if ($isToSpecificUser) {
            $results = $this->list($tableName, null, $option->addStaticQuery("iD = '$iD'"));
        }

        if (!empty($results)) {
            if ($topic) {
                $response = $this->sendNotification($notificationObject, null, $topic);
            } else {
                foreach ($results as &$i) {
                    $i['response'] =   $this->sendNotification($notificationObject, Helpers::getKeyValueFromObj($i, "token"));
                    $response[] = $i;
                }
            }
        }
        return $response;
    }
    public function getToken($iD, $tableName)
    {
        return Helpers::getKeyValueFromObj($this->view($tableName, $iD), "token");
    }
    public function getNotificationObject($obj)
    {
        return [
            'title' => Helpers::getKeyValueFromObj($obj, "title"),
            'body' => Helpers::getKeyValueFromObj($obj, "body"),
        ];
    }

    public function sendNotification($obj, ?string $tokenID = null, ?string $topic = null)
    {
        try {
            $credential = new ServiceAccountCredentials(
                "https://www.googleapis.com/auth/firebase.messaging",
                json_decode(file_get_contents(__DIR__ . "/../../pvKey.json"), true)
            );

            $token = $credential->fetchAuthToken(HttpHandlerFactory::build());

            $ch = curl_init(FB_URL);

            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token['access_token']
            ]);
            $fcmNotification = array();
            if ($topic) {


                $fcmNotification = [
                    'message' => [
                        "topic" => $topic,
                        'notification' => $this->getNotificationObject($obj),
                    ],
                ];
            } else {
                $fcmNotification = [
                    'message' => [
                        "token" => $tokenID,
                        'notification' => $this->getNotificationObject($obj),
                    ],
                ];
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "post");

            $response = curl_exec($ch);

            curl_close($ch);
            echo "ssososososos";
            if ($response) {

                return json_decode($response);
            } else {
                return ["error" => [
                    "code" => 503,
                    "message" => "unknown error",
                ]];
            }
        } catch (\Exception $e) {

            return ["error" => [
                "code" => 503,
                "message" =>
                $e->getMessage(),
            ]];
        }
    }
}
