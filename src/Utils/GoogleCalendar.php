<?php

namespace App\Utils;

class GoogleCalendar{

    const COLOR_GREY    = 8;
    const COLOR_GREEN   = 2;
    const COLOR_RED     = 11;

    const SECRET_PATH   = __DIR__ . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."google_secret.json";
    const TOKEN_PATH    = __DIR__ . DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."google_token.json";

    private $client;
    private $service;

    /**
     * Constructor
     *
     * @param bool $thowError - Throw error if google refresh token is expired - true by default
     */
    public function __construct(bool $throwError = true)
    {
        try {
            $client = new \Google_Client();
            $client->setApplicationName("Icademie Suivi Pedagogique");
            $client->setScopes(\Google_Service_Calendar::CALENDAR);
            $client->setAuthConfig(GoogleCalendar::SECRET_PATH);
            $client->setAccessType("offline");
            $client->setPrompt("select_account consent");

            // If google_token.json exist
            if(file_exists(GoogleCalendar::TOKEN_PATH)) {
                $accessToken = json_decode(file_get_contents(GoogleCalendar::TOKEN_PATH), true);
                $client->setAccessToken($accessToken);
            }

            // If token is expired or does not exist
            if($client->isAccessTokenExpired()) {

                // If refresh token is still valid
                if($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

                    if(!file_exists(dirname(GoogleCalendar::TOKEN_PATH))) {
                        mkdir(dirname(GoogleCalendar::TOKEN_PATH), 0700, true);
                    }

                    file_put_contents(GoogleCalendar::TOKEN_PATH, json_encode($client->getAccessToken()));
                }
                else {
                    if($throwError) throw new \Exception("Cannot refresh google token");
                }
            }

            $this->client = $client;
            $this->service = new \Google_Service_Calendar($client);
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Google_Client object
     *
     * @return \Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get Google_Service_Calendar object
     *
     * @return \Google_Service_Calendar
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Get Google Calendar Event object
     *
     * @param string $eventId - Google Calendar Event id
     * @param string $calendarId - Google Calendar id
     *
     * @return \Event
     */
    public function getEvent(string $eventId, string $calendarId)
    {
        try {
            return $this->service->events->get($calendarId, $eventId);
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Create Google Calendar Event object
     *
     * @param string $calendarId - Google Calendar id
     * @param string $titre - Google Calendar event title
     * @param string $description - Google Calendar event description
     * @param \DateTime $debut - Google Calendar event start date
     * @param \DateTime $fin - Google Calendar event end date
     *
     * @return \Event
     */
    public function createEvent(string $calendarId, string $titre, string $description, \DateTime $debut, \DateTime $fin)
    {
        try {
            $event = new \Google_Service_Calendar_Event(array(
                "summary" => $titre,
                "description" => $description,
                "start" => array(
                    "dateTime" => $debut->format(\DateTime::RFC3339)
                ),
                "end" => array(
                    "dateTime" => $fin->format(\DateTime::RFC3339)
                ),
            ));

            return $this->service->events->insert($calendarId, $event);
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update Google Calendar Event object
     *
     * @param string $eventId - Google Calendar Event id
     * @param string $calendarId - Google Calendar id
     * @param string $titre - Google Calendar event title
     * @param string $description - Google Calendar event description
     * @param \DateTime $debut - Google Calendar event start date
     * @param \DateTime $fin - Google Calendar event end date
     *
     * @return \Event
     */
    public function updateEvent(string $eventId, string $calendarId, string $titre, string $description, \DateTime $debut, \DateTime $fin)
    {
        try {
            $event = new \Google_Service_Calendar_Event(array(
                "summary" => $titre,
                "description" => $description,
                "start" => array(
                    "dateTime" => $debut->format(\DateTime::RFC3339)
                ),
                "end" => array(
                    "dateTime" => $fin->format(\DateTime::RFC3339)
                )
            ));

            $updatedEvent = $this->service->events->update($calendarId, $eventId, $event);

            return $updatedEvent->getUpdated();
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete Google Calendar Event object
     *
     * @param string $eventId - Google Calendar Event id
     * @param string $calendarId - Google Calendar id
     *
     * @return \Event
     */
    public function deleteEvent(string $eventId, string $calendarId)
    {
        try {
            $this->service->events->delete($calendarId, $eventId);
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Google Calendar object
     *
     * @param string $calendarId - Google Calendar id
     *
     * @return \Calendar
     */
    public function getCalendar(string $calendarId)
    {
        try {
            return $this->service->calendars->get($calendarId);
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get a list of all Google Calendars from the connected account
     *
     * @return array
     */
    public function getCalendars()
    {
        try {
            $response = [];
            $nextPageToken = true;

            $listCalendars = $this->service->calendarList->listCalendarList();

            while($nextPageToken) {
                foreach($listCalendars->getItems() as $calendar) {
                    $response[] = $calendar;
                }

                $nextPageToken = $listCalendars->getNextPageToken();

                if($nextPageToken) {
                    $listCalendars = $this->service->calendarList->listCalendarList(["pageToken" => $nextPageToken]);
                }
            }

            return $response;
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get a list of all Google Calendars from the connected account, parsed
     *
     * Exemple :
     *
     * [
     *      "calendarId" => "calendarSummary",
     *      "calendarId" => "calendarSummary"...
     * ]
     *
     * @return array
     */
    public function getCalendarsParsed()
    {
        try {
            $response = [];
            $nextPageToken = true;

            $listCalendars = $this->service->calendarList->listCalendarList();

            while($nextPageToken) {
                foreach($listCalendars->getItems() as $calendar) {
                    $response[$calendar->getId()] = $calendar->getSummary();
                }

                $nextPageToken = $listCalendars->getNextPageToken();

                if($nextPageToken) {
                    $listCalendars = $this->service->calendarList->listCalendarList(["pageToken" => $nextPageToken]);
                }
            }

            return $response;
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the .ics download link for a Google Calendar
     *
     * @param string $calendarId - Google Calendar id
     *
     * @return string
     */
    public function getCalendarDownloadLink(string $calendarId)
    {
        $id = substr($calendarId, 0, strpos($calendarId, '@'));

        return "https://calendar.google.com/calendar/ical/" . $id ."%40group.calendar.google.com/public/basic.ics";
    }
}

?>